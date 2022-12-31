import React from 'react';
import { createRoot } from 'react-dom/client';
import { DateTime } from 'luxon';

import BookBrowserApp from './apps/book-browser';
import inject from './Injector';

import './index.scss';
import './components/Tengwar.scss'; // Tengwar is scattered across the website, so this will ensure all will render appropriately.
import bootstrapServerSideRenderedBootstrapComponents from './utilities/BootstrapBootstrapper';

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
        const root = createRoot(container!);
        root.render(<BookBrowserApp />);
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
        inject();
    });
}
