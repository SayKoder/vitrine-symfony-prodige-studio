import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['scene', 'image', 'marqueur', 'apercu', 'champX', 'champY'];

    connect() {
        this.x = this.borner(parseInt(this.champXTarget.value, 10));
        this.y = this.borner(parseInt(this.champYTarget.value, 10));
        this.deplacementEnCours = false;
        this.rendre();
    }

    pointer(event) {
        if ('pointerdown' === event.type) {
            this.deplacementEnCours = true;
            this.sceneTarget.setPointerCapture(event.pointerId);
        }

        if (!this.deplacementEnCours) {
            return;
        }

        event.preventDefault();

        const zone = this.sceneTarget.getBoundingClientRect();
        this.x = this.borner(Math.round(((event.clientX - zone.left) / zone.width) * 100));
        this.y = this.borner(Math.round(((event.clientY - zone.top) / zone.height) * 100));
        this.rendre();
    }

    relacher() {
        this.deplacementEnCours = false;
    }

    rendre() {
        const position = `${this.x}% ${this.y}%`;

        this.champXTarget.value = this.x;
        this.champYTarget.value = this.y;
        this.marqueurTarget.style.left = `${this.x}%`;
        this.marqueurTarget.style.top = `${this.y}%`;
        this.apercuTargets.forEach((image) => {
            image.style.objectPosition = position;
        });
    }

    borner(valeur) {
        if (Number.isNaN(valeur)) {
            return 50;
        }

        return Math.max(0, Math.min(100, valeur));
    }
}
