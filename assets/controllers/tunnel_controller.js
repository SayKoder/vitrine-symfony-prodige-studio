import { Controller } from '@hotwired/stimulus';

const CLE_CODE_PROMO = 'vitrineps_code_promo';

export default class extends Controller {
    static targets = [
        'recap', 'lignes', 'vide', 'promo', 'total', 'erreur',
        'validerBouton', 'confirmation', 'confirmationTexte',
    ];

    connect() {
        this.panierVide = true;
        this.charger();
    }

    async charger() {
        const reponse = await fetch('/api/paniers/mine', {
            headers: { Accept: 'application/json' },
        });

        if (!reponse.ok) {
            return;
        }

        const panier = await reponse.json();
        this.afficher(panier);
        await this.revaliderPromo();
    }

    afficher(panier) {
        const lignes = panier.lignesPanier ?? [];
        this.panierVide = lignes.length === 0;

        this.lignesTarget.innerHTML = '';
        this.videTarget.hidden = lignes.length > 0;
        this.validerBoutonTarget.hidden = this.panierVide;

        let total = 0;

        lignes.forEach((ligne) => {
            const prix = parseFloat(ligne.prestation.prix);
            const sousTotal = prix * ligne.quantite;
            total += sousTotal;

            const carte = document.createElement('div');
            carte.className = 'cadre-coin panier-ligne panier-ligne--lecture-seule';
            carte.innerHTML = `
                <div class="panier-ligne-info">
                    <p class="panier-ligne-nom">${ligne.prestation.nom}</p>
                    <p class="panier-ligne-prix">${ligne.quantite} &times; ${prix.toFixed(2)}&euro;</p>
                </div>
                <p class="panier-ligne-sous-total">${sousTotal.toFixed(2)}&euro;</p>
            `;
            this.lignesTarget.appendChild(carte);
        });

        this.totalTarget.textContent = `${total.toFixed(2)}€`;
    }

    async revaliderPromo() {
        const code = window.sessionStorage.getItem(CLE_CODE_PROMO);

        if (!code || this.panierVide) {
            this.promoTarget.hidden = true;
            return;
        }

        const reponse = await fetch('/api/promo_codes/valider', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code }),
        });

        if (!reponse.ok) {
            window.sessionStorage.removeItem(CLE_CODE_PROMO);
            this.promoTarget.hidden = true;
            return;
        }

        const donnees = await reponse.json();
        this.promoTarget.hidden = false;
        this.promoTarget.textContent = `Code ${donnees.code} applique : -${donnees.montantReduction}€, total apres reduction ${donnees.totalApresReduction}€.`;
        this.totalTarget.textContent = `${donnees.totalApresReduction}€`;
    }

    async valider() {
        this.erreurTarget.hidden = true;
        this.validerBoutonTarget.disabled = true;
        this.validerBoutonTarget.textContent = 'VALIDATION EN COURS...';

        const codePromo = window.sessionStorage.getItem(CLE_CODE_PROMO);

        const reponse = await fetch('/api/commandes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ codePromo }),
        });

        if (!reponse.ok) {
            const donnees = await reponse.json().catch(() => ({}));
            this.erreurTarget.hidden = false;
            this.erreurTarget.textContent = donnees.message ?? 'Impossible de valider la commande, reessayez.';
            this.validerBoutonTarget.disabled = false;
            this.validerBoutonTarget.textContent = 'VALIDER MA COMMANDE';
            return;
        }

        const commande = await reponse.json();
        window.sessionStorage.removeItem(CLE_CODE_PROMO);

        this.recapTarget.hidden = true;
        this.confirmationTarget.hidden = false;
        this.confirmationTexteTarget.textContent = `Commande n°${commande.id} d'un montant de ${commande.total}€. Un e-mail de confirmation vous a ete envoye.`;
    }
}
