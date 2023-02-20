import { DateTime } from 'luxon';

import { EuTimezones } from '@root/config';
import CookieConsent from './containers/CookieConsent';

export default function () {
    const zone = DateTime.now().zoneName;
    if (EuTimezones.indexOf(zone) === -1) {
        return null;
    }

    return <CookieConsent zone={zone} />;
}
