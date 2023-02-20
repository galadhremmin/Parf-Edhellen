import { useState } from 'react';

import Dialog from '@root/components/Dialog';
import Overview from '../components/Overview';
import Settings from '../components/Settings';

const enum ConsentView {
    Overview = 'overview',
    Settings = 'settings',
};

export default function CookieConsent({ zone }: { zone: string }) {
    const [ view, setView ] = useState<ConsentView>(ConsentView.Overview);

    const _onConsent = () => {

    };
    const _onSettings = () => {
        setView(ConsentView.Settings);
    };

    return <Dialog open={true}
                   dismissable={false}
                   title="We use cookies"
                   confirmButtonText="I consent"
                   onConfirm={_onConsent}>
        {view === ConsentView.Overview && <Overview onSettings={_onSettings} />}
        {view === ConsentView.Settings && <Settings />}
    </Dialog>;
}
