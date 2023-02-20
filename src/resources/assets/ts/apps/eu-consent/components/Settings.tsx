import TextIcon from '@root/components/TextIcon';
import {
    useState,
    ChangeEvent,
    useEffect
} from 'react';

const CookieUseCases = [
    {
        domain: 'Essential',
        description: 'Essential cookies for basic functionality on the website, including protecting against cross-site request forgery (CSRF). These cookies are required for the website to function and we will assume implicit consent.',
        readonly: true,
        regex: '^(laravel_session|XSRF\-TOKEN)$'
    },
    {
        domain: 'Analytics',
        description: 'Helps us understand how our guests interact with the website so we can find issues and improve the user experience in the future.',
        readonly: false,
        regex: '^_ga(_.+)?$',
        readMore: 'https://support.google.com/analytics/answer/11397207?hl=en',
    },
    {
        domain: 'Advertising',
        description: 'Third-party cookies that personalizes ads and improves their relevancy. The purpose is to advertise things you might be interested in.',
        readonly: false,
        regex: '^()$',
        readMore: 'https://business.safety.google/adscookies/',
    },
];

export default function Settings() {
    const [ cookieDomains, setCookieDomains ] = useState<boolean[]>(() => {
        return CookieUseCases.map((useCase) => useCase.readonly);
    });

    const [ cookies, setCookies ] = useState<string>();
    useEffect(() => {

        // TODO: implement this capability using a `Cookie` class

    }, [ document.cookie ]);

    const _onChange = (ev: ChangeEvent<HTMLInputElement>) => {
        const checked = ev.target.checked;
        const domain = ev.target.value;
        const nextCookieDomains = [ ...cookieDomains ];
        nextCookieDomains[CookieUseCases.findIndex(useCase => useCase.domain === domain)] = checked;
        setCookieDomains(nextCookieDomains);
    };

    return <>
        <p>
            Check the types of cookies you cookies you consent to us storing on your device.
        </p>
        {CookieUseCases.map((useCase, i) => <p key={useCase.domain}>
            <label>
                <input type="checkbox"
                    checked={cookieDomains[i]}
                    disabled={useCase.readonly}
                    name="cookie-domains" 
                    value={useCase.domain}
                    onChange={_onChange}
                /> <strong>{useCase.domain}</strong>
            </label>
            <a href="" className="float-end"><TextIcon icon="chevron-up" /></a>
            : {useCase.description}
        </p>)}
    </>;
}
