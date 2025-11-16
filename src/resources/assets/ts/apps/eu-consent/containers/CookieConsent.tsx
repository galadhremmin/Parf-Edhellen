import { useState } from 'react';

import type { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import {
    CookieUseCases,
    EuConsentCookieName,
    EuConsentCookieSelection,
    EuConsentGivenCookieValue,
} from '@root/config';

import Overview from '../components/Overview';
import Settings from '../components/Settings';
import Cookies from 'js-cookie';
import { DateTime } from 'luxon';

const enum ConsentView {
    Overview = 'overview',
    Settings = 'settings',
}

export default function CookieConsent({ zone }: { zone: string }) {
    const [ view, setView ] = useState<ConsentView>(ConsentView.Overview);
    const [ cookieUseCasesConsent, setCookieUseCasesConsent ] = useState<string[]>( //
        () => CookieUseCases.map((c) => c.scriptName), //
    );

    const _onConsent = () => {
        const config = {
            secure: true,
            path: '/',
            expires: DateTime.now().plus({ years: 2 }).toJSDate(),
        };
        Cookies.set(EuConsentCookieName, EuConsentGivenCookieValue, config);
        Cookies.set(EuConsentCookieSelection, cookieUseCasesConsent.join('|'), config);

        window.location.reload();
    };
    const _onSettings = () => {
        setView(ConsentView.Settings);
    };
    const _onConsentChange = (ev: IComponentEvent<string[]>) => {
        setCookieUseCasesConsent(ev.value);
    }

    return <Dialog open={true}
                   dismissable={false}
                   title="We use cookies"
                   confirmButtonText="OK"
                   onConfirm={_onConsent}>
        {view === ConsentView.Overview && <Overview onSettings={_onSettings} />}
        {view === ConsentView.Settings && <Settings
            consentedUseCases={cookieUseCasesConsent}
            onConsentedUseCasesChange={_onConsentChange}
        />}
    </Dialog>;
}
