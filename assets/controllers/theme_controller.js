import { Controller } from '@hotwired/stimulus';

const CLE_STOCKAGE = 'vitrineps-theme';

export default class extends Controller {
    connect() {
        const themeEnregistre = window.localStorage.getItem(CLE_STOCKAGE);
        if (themeEnregistre === 'light' || themeEnregistre === 'dark') {
            document.documentElement.setAttribute('data-theme', themeEnregistre);
        }
    }

    basculer() {
        const themeActuel = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        const themeSuivant = themeActuel === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', themeSuivant);
        window.localStorage.setItem(CLE_STOCKAGE, themeSuivant);
    }
}
