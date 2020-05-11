import React from 'react';
import { GlobalAdsConfigurationName } from '@root/config';

import Ad from './containers/Ad';
import {
    IGlobalAdConfiguration,
    IGlobalAdsConfiguration,
    IProps,
} from './index._types';

const Inject = (props: IProps) => {
    const {
        ad,
    } = props;

    if (! window?.hasOwnProperty(GlobalAdsConfigurationName)) {
        return null;
    }

    const adConfigs = (window as any)[GlobalAdsConfigurationName] as IGlobalAdsConfiguration;
    const config = adConfigs[ad];
    return config ? <Ad {...config} onMount={adConfigs._mount || null} /> : null;
};

export default Inject;
