import React from 'react';
import { render } from 'react-dom';

import BookBrowserApp from './apps/book-browser';
import inject from './Injector';

import './index.scss';
import './components/Tengwar.scss'; // Tengwar is scattered across the website, so this will ensure all will render appropriately.
import DateLabel from './components/DateLabel';
import bootstrapServerSideRenderedBootstrapComponents from './utilities/BootstrapBootstrapper';

const loadLatestScript = () => {
    const scriptTag = document.currentScript as HTMLScriptElement;
    if (! scriptTag) {
        return true;
    }

    const latestVersion = document.body.dataset[`v`];
    const latestScriptReg = new RegExp(`\/v${latestVersion}\/index\.js$`);
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
        render(<BookBrowserApp />, document.getElementById('ed-search-component'));
    };

    const renderDates = () => {
        const dateElements = document.querySelectorAll<HTMLElement>('time');
        dateElements.forEach((dateElement: HTMLTimeElement) => {
            const date = dateElement.dateTime.trim();
            if (date.length > 0) {
                render(<DateLabel dateTime={date} ignoreTag={true} />, dateElement);
                dateElement.classList.add('opacity-100');
            }
        });
    };

    window.addEventListener('load', () => {
        bootstrapServerSideRenderedBootstrapComponents();
        renderDates();
        renderDictionary();
        inject();
    });
}
