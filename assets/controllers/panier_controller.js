import { Controller } from '@hotwired/stimulus';

const CLE_CODE_PROMO = 'vitrineps_code_promo';

export default class extends Controller {
    static targets = ['lignes', 'vide', 'total', 'promoCode', 'promoResultat', 'validerLien'];

    connect() {
        this.charger();
    }

    async charger() {
        const reponse = await fetch('/api/paniers/mine', {
            headers: { Accept: 'application/json' },
        });

        if (!reponse.ok) {
            return;
        }

        this.afficher(await reponse.json());
    }

    afficher(panier) {
        const lignes = panier.lignesPanier ?? [];

        this.lignesTarget.innerHTML = '';
        this.videTarget.hidden = lignes.length > 0;
        this.validerLienTarget.hidden = lignes.length === 0;

        let total = 0;

        lignes.forEach((ligne) => {
            const prix = parseFloat(ligne.prestation.prix);
            const sousTotal = prix * ligne.quantite;
            total += sousTotal;

            const carte = document.createElement('div');
            carte.className = 'cadre-coin panier-ligne';
            carte.innerHTML = `
                <div class="panier-ligne-info">
                    <p class="panier-ligne-nom">${ligne.prestation.nom}</p>
                    <p class="panier-ligne-prix">${prix.toFixed(2)}&euro; / unite</p>
                </div>
                <div class="panier-ligne-quantite">
                    <button type="button" data-action="panier#diminuer" data-id="${ligne.id}" data-quantite="${ligne.quantite}" aria-label="Diminuer la quantite">-</button>
                    <span>${ligne.quantite}</span>
                    <button type="button" data-action="panier#augmenter" data-id="${ligne.id}" data-quantite="${ligne.quantite}" aria-label="Augmenter la quantite">+</button>
                </div>
                <p class="panier-ligne-sous-total">${sousTotal.toFixed(2)}&euro;</p>
                <button type="button" class="panier-ligne-retirer" data-action="panier#supprimer" data-id="${ligne.id}" aria-label="Retirer cette prestation">&times;</button>
            `;
            this.lignesTarget.appendChild(carte);
        });

        this.totalTarget.textContent = `${total.toFixed(2)}€`;
    }

    augmenter(event) {
        const { id, quantite } = event.currentTarget.dataset;
        this.majQuantite(id, parseInt(quantite, 10) + 1);
    }

    diminuer(event) {
        const { id, quantite } = event.currentTarget.dataset;

        if (parseInt(quantite, 10) <= 1) {
            this.supprimer(event);
            return;
        }

        this.majQuantite(id, parseInt(quantite, 10) - 1);
    }

    async majQuantite(id, quantite) {
        await fetch(`/api/ligne_paniers/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/merge-patch+json' },
            body: JSON.stringify({ quantite }),
        });

        await this.charger();
    }

    async supprimer(event) {
        const { id } = event.currentTarget.dataset;

        await fetch(`/api/ligne_paniers/${id}`, { method: 'DELETE' });
        await this.charger();
    }

    async validerPromo(event) {
        event.preventDefault();
        const code = this.promoCodeTarget.value.trim();

        if (!code) {
            return;
        }

        const reponse = await fetch('/api/promo_codes/valider', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code }),
        });

        const donnees = await reponse.json();

        this.promoResultatTarget.classList.toggle('panier-promo-erreur', !reponse.ok);

        if (!reponse.ok) {
            this.promoResultatTarget.textContent = donnees.message ?? 'Code invalide.';
            window.sessionStorage.removeItem(CLE_CODE_PROMO);
            return;
        }

        window.sessionStorage.setItem(CLE_CODE_PROMO, donnees.code);
        this.promoResultatTarget.textContent = `Code ${donnees.code} applique : -${donnees.montantReduction}€, nouveau total ${donnees.totalApresReduction}€.`;
    }
}
