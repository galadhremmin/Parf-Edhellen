import { getLocalTimeZone } from '@root/utilities/DateTime';

import { TimezonesWithEuConsent } from '@root/config';
import CookieConsent from './containers/CookieConsent';
import registerApp from '../app';

export default registerApp(function EuConsent() {
    const zone = getLocalTimeZone();
    if (zone.startsWith('Europe/') || TimezonesWithEuConsent.indexOf(zone) === -1) {
        return null;
    }

    return <CookieConsent zone={zone} />;
});
