import React from 'react';
import { GlobalAdsConfigurationName } from '@root/config';

import Ad from './containers/Ad';
import {
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

    const config = ((window as any)[GlobalAdsConfigurationName] as IGlobalAdsConfiguration)[ad];
    return config ? <Ad config={config} /> : null;
};

export default Inject;
