import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menuMobile'];
    static classes = ['reduite'];

    connect() {
        this.onScroll = this.onScroll.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.onScroll();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
    }

    onScroll() {
        this.element.classList.toggle(this.reduiteClass, window.scrollY > 72);
    }

    basculerMenu() {
        this.menuMobileTarget.classList.toggle('ouvert');
    }

    fermerMenu() {
        this.menuMobileTarget.classList.remove('ouvert');
    }
}
