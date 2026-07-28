import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['carte', 'filtre'];

    filtrer(event) {
        const categorie = event.currentTarget.dataset.categorie;

        this.filtreTargets.forEach((bouton) => {
            bouton.classList.toggle('actif', bouton === event.currentTarget);
        });

        this.carteTargets.forEach((carte) => {
            const correspond = 'tous' === categorie || carte.dataset.categorie === categorie;
            carte.style.display = correspond ? '' : 'none';
        });
    }
}
