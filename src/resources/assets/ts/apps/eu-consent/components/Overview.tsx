import { MouseEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './Overview._types';

export default function Overview({ onSettings }: IProps) {
    const _onSettings = (ev: MouseEvent) => {
        ev.preventDefault();
        fireEvent('Overview', onSettings);
    };

    return <>
        <p>
            We use our own (first-party) and partners' (third-party) cookies on this website. We need your consent to proceed.
        </p>
        <ul>
            <li><strong>First party cookies</strong> are used to enable you to use this website, and to protect against cross-site request forgery (CSRF).</li>
            <li><strong>Third-party cookies</strong> are used for statistics and web analysis, as well as advertising. We depend on advertising to cover our maintenance costs.</li>
        </ul>
        <p>
            By tapping <em>I consent,</em> you approve of our use of cookies.
            If you want to fine-tune the kind of third-party cookies we can use, <a href="#" onClick={_onSettings}>go to the settings page</a>.
        </p>
    </>;
}
