import { formatDateTimeFull, fromISOToDate } from './utilities/DateTime';

import inject from './Injector';
import setupContainer from './di/config';
import bootstrapServerSideRenderedBootstrapComponents from './utilities/BootstrapBootstrapper';
import { GlobalEventLoadReference } from './config';

import './components/Tengwar.scss'; // Tengwar is scattered across the website, so this will ensure all will render appropriately.
import './index.scss';

function loadLatestScript() {
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
    latestScript.type = 'text/javascript';
    document.currentScript?.parentNode?.removeChild(document.currentScript);
    document.body.appendChild(latestScript);
    return false;
}

/**
 * Converts server-side rendered UTC times into local time. This operation is only
 * updating the title of the element, so no reflow should be necessary.
 */
function renderDates() {
    const dateElements = document.querySelectorAll<HTMLElement>('time:not(.react)');
    for (let i = 0; i < dateElements.length; i += 1) {
        const dateElement = dateElements.item(i) as HTMLTimeElement;
        const date = dateElement.dateTime.trim();
        const d = fromISOToDate(date);
        if (d) {
            dateElement.title = formatDateTimeFull(d);
        }
    }
}

function initPopularSearches() {
    const list = document.getElementById('popular-searches');
    if (!list) {
        return;
    }

    list.addEventListener('click', (e) => {
        const link = (e.target as Element).closest<HTMLAnchorElement>('a[data-word]');
        if (!link) {
            return;
        }
        e.preventDefault();
        window.dispatchEvent(new CustomEvent(GlobalEventLoadReference, {
            detail: {
                word: link.dataset.word,
                normalizedWord: link.dataset.word,
                languageShortName: link.dataset.languageShortName ?? '',
                updateBrowserHistory: true,
            },
        }));
    });
}

function globalOrchestration() {
    setupContainer();
    bootstrapServerSideRenderedBootstrapComponents();
    renderDates();
    initPopularSearches();
    inject().catch(error => {
        console.error('Application bootstrapping failed.', error);
    });
}

if (loadLatestScript()) {
    const boot = () => {
        void globalOrchestration();
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        boot();
    } else {
        window.addEventListener('DOMContentLoaded', boot, { once: true });
    }
}
