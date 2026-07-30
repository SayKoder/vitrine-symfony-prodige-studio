import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { message: String };

    confirmer(event) {
        if (!window.confirm(this.messageValue)) {
            event.preventDefault();
        }
    }
}
