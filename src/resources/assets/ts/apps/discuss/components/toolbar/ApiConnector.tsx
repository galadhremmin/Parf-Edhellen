import React from 'react';

import IDiscussApi from '@root/connectors/backend/IDiscussApi';
import { DI, resolve } from '@root/di';

function connectApi<TProps extends {
    apiConnector?: IDiscussApi,
}>(component: React.FunctionComponent<TProps> | React.ComponentClass<TProps>) {
    const existingProps = component.defaultProps || {};

    component.defaultProps = Object.assign({}, existingProps, {
        apiConnector: resolve(DI.DiscussApi),
    }) as Partial<TProps>;

    return component;
}

export default connectApi;
