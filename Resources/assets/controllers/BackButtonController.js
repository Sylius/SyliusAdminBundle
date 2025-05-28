/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        currentUrl: String,
        fallbackUrl: String
    };

    connect() {
        const referrer = document.referrer;
        const currentUrl = this.currentUrlValue;

        if (referrer && referrer !== currentUrl) {
            sessionStorage.setItem('back_button_last_url', referrer);
        }
    }

    goBack() {
        const currentUrl = this.currentUrlValue;
        const previousUrl = sessionStorage.getItem('back_button_last_url');

        if (previousUrl && previousUrl !== currentUrl) {
            window.location.href = previousUrl;
        } else if (this.hasFallbackUrlValue) {
            window.location.href = this.fallbackUrlValue;
        } else {
            console.warn('No previous URL and no fallback provided for back button.');
        }
    }
}
