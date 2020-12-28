import React from 'react';
import { GlobalAdsConfigurationName } from '@root/config';

import Ad from './containers/Ad';
import Placeholder from './containers/Placeholder';
import {
    IGlobalAdsConfiguration,
    IProps,
} from './index._types';

const Inject = (props: IProps) => {
    const {
        ad,
    } = props;

    if (! window?.hasOwnProperty(GlobalAdsConfigurationName)) {
        return <Placeholder ad={ad} />;
    }

    const adConfigs = (window as any)[GlobalAdsConfigurationName] as IGlobalAdsConfiguration;
    const config = adConfigs[ad];
    return config ? <Ad ad={ad} {...config} onMount={adConfigs._mount || null} /> : null;
};

export default Inject;
