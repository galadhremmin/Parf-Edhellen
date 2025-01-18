/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import { GlobalAdsConfigurationName } from '@root/config';

import Ad from './containers/Ad';
import Placeholder from './containers/Placeholder';
import {
    WindowWithAds,
    IProps,
} from './index._types';
import registerApp from '../app';

const Inject = (props: IProps) => {
    const {
        ad,
    } = props;

    // eslint-disable-next-line no-prototype-builtins
    if (! window?.hasOwnProperty(GlobalAdsConfigurationName)) {
        return <Placeholder ad={ad} />;
    }

    const adConfigs = (window as WindowWithAds)[GlobalAdsConfigurationName];
    const config = adConfigs[ad];
    return config ? <Ad ad={ad} {...config} onMount={adConfigs._mount || null} /> : null;
};

export default registerApp(Inject);
