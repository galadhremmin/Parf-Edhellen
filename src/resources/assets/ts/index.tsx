import { createRoot, hydrateRoot } from 'react-dom/client';
import { DateTime } from 'luxon';

import BookBrowserApp from './apps/book-browser';
import EuConsent from './apps/eu-consent';
import inject from './Injector';

import './index.scss';
import './components/Tengwar.scss'; // Tengwar is scattered across the website, so this will ensure all will render appropriately.
import bootstrapServerSideRenderedBootstrapComponents from './utilities/BootstrapBootstrapper';
import Cookies from 'js-cookie';
import { EuConsentCookieName, EuConsentExemptionPaths, EuConsentGivenCookieValue } from './config';

const loadLatestScript = () => {
    const scriptTag = document.currentScript as HTMLScriptElement;
    if (! scriptTag) {
        return true;
    }

    const latestVersion = document.body.dataset[`v`];
    const latestScriptReg = new RegExp(`/v${latestVersion}/index.js$`);
    if (latestScriptReg.test(scriptTag.src)) {
        console.info(`🧙‍♂️ Parf Edhellen version ${latestVersion}`);
        return true;
    }

    console.warn(`Detected outdated version - sideloading ${latestVersion}!`);
    const latestScript = document.createElement('script');
    latestScript.src = `/v${latestVersion}/index.js`;
    document.body.appendChild(latestScript);
    return false;
};

if (loadLatestScript()) {
    /**
     * Render the website's most important component.
     */
    const renderDictionary = () => {
        const container = document.getElementById('ed-search-component');
        if (container.children.length > 0) {
            hydrateRoot(container, <BookBrowserApp />);
        } else {
            const root = createRoot(container);
            root.render(<BookBrowserApp />);
        }
    };

    /**
     * Cookie consent dialogue as required by the European Union.
     */
    const renderEuConsent = () => {
        // If consent is already given, don't ask again!
        if (Cookies.get(EuConsentCookieName) === EuConsentGivenCookieValue) {
            return;
        }

        // If the user is viewing an exempted page, don't ask.
        if (EuConsentExemptionPaths.indexOf(location.pathname) > -1) {
            return;
        }

        const container = document.getElementById('ed-eu-consent');
        if (container) {
            const root = createRoot(container);
            root.render(<EuConsent />);
        }
    };

    /**
     * Converts server-side rendered UTC times into local time. This operation is only
     * updating the title of the element, so no reflow should be necessary.
     */
    const renderDates = () => {
        const dateElements = document.querySelectorAll<HTMLElement>('time:not(.react)');
        for (let i = 0; i < dateElements.length; i += 1) {
            const dateElement = dateElements.item(i) as HTMLTimeElement;
            const date = dateElement.dateTime.trim();
            dateElement.title = DateTime.fromISO(date).toLocaleString(DateTime.DATETIME_FULL);
        }
    };

    window.addEventListener('load', () => {
        bootstrapServerSideRenderedBootstrapComponents();
        renderDates();
        renderDictionary();
        renderEuConsent();
        inject();
    });
}
