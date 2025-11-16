import { formatDateTimeFull, fromISOToDate } from './utilities/DateTime';

import inject from './Injector';
import setupContainer from './di/config';
import bootstrapServerSideRenderedBootstrapComponents from './utilities/BootstrapBootstrapper';

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
    latestScript.onload = () => {
        void globalOrchestration(); 
    };
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

function globalOrchestration() {
    setupContainer();
    bootstrapServerSideRenderedBootstrapComponents();
    renderDates();
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
