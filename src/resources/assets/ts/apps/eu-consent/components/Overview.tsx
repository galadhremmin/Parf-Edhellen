import type { MouseEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import type { IProps } from './Overview._types';
import { CommonPaths } from '@root/config';

export default function Overview({ onSettings }: IProps) {
    const _onSettings = (ev: MouseEvent) => {
        ev.preventDefault();
        void fireEvent('Overview', onSettings);
    };

    return <>
        <p>
            We use our own (first-party) and partners' (third-party) cookies on this website. We need your consent to proceed.
        </p>
        <ul>
            <li><strong>First party cookies</strong> are used to enable you to use this website, and to protect against cross-site request forgery (CSRF).</li>
            <li><strong>Third party cookies</strong> are used for statistics and advertising. Third party cookies may contain information about you from other websites to personalize ads.</li>
        </ul>
        <p>
            You can learn more by reading our <a href={CommonPaths.privacyPolicy}>privacy policy</a>. By tapping <em>OK,</em> you approve the use of these cookies.{' '}
            If you want to pick what cookies we can use, <a href="#" onClick={_onSettings}>go to the settings page</a>.
        </p>
    </>;
}
