import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['bouton'];
    static values = { prestation: String };

    async ajouter() {
        const texteInitial = this.boutonTarget.textContent;
        this.boutonTarget.disabled = true;

        const reponse = await fetch('/api/ligne_paniers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prestation: this.prestationValue, quantite: 1 }),
        });

        this.boutonTarget.textContent = reponse.ok ? 'AJOUTE !' : 'ERREUR, REESSAYEZ';

        window.setTimeout(() => {
            this.boutonTarget.textContent = texteInitial;
            this.boutonTarget.disabled = false;
        }, 1800);
    }
}
