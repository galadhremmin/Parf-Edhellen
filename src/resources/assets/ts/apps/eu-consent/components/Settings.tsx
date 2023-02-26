import React from 'react';

import TextIcon from '@root/components/TextIcon';
import { fireEvent } from '@root/components/Component';
import {
    AdvertisingUseCaseScriptName,
    CookieUseCases,
} from '@root/config';

import { IProps } from './Settings._types';

export default function Settings(props: IProps) {
    const {
        consentedUseCases,
        onConsentedUseCasesChange,
    } = props;
    const _onChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const checked = ev.target.checked;
        const useCase = ev.target.value;
        const nextConsentedUseCases = consentedUseCases.filter((c) => c !== useCase);
        if (checked) {
            nextConsentedUseCases.push(useCase);
        }
        fireEvent('Settings', onConsentedUseCasesChange, nextConsentedUseCases);
    };

    return <>
        <p>
            Uncheck the types of cookies you do not consent our storing on your device.
        </p>
        {CookieUseCases.map((useCase) => <p key={useCase.domain}>
            <label>
                <input type="checkbox"
                    checked={consentedUseCases.indexOf(useCase.scriptName) !== -1}
                    disabled={useCase.readonly}
                    name="cookie-domains" 
                    value={useCase.scriptName}
                    onChange={_onChange}
                /> <strong>{useCase.domain}</strong>
            </label>
            : {` ${useCase.description} `}
            {useCase.readMore && <a href={useCase.readMore} target="_blank" rel="noreferrer">
                Read more about these cookies
            </a>}.
        </p>)}
        {consentedUseCases.indexOf(AdvertisingUseCaseScriptName) === -1 && <p>
            <TextIcon icon="info-sign" /> <strong>Note:</strong> We depend on advertising to support
            the continued development and maintenance of this website. We will not earn any ad revenue
            if you do not consent to advertising.
        </p>}
    </>;
}
