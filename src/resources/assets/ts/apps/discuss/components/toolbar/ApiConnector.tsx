import React from 'react';

import { DI, resolve } from '@root/di';

function connectApi<TProps>(component: React.SFC<TProps>) {
    const existingProps = component.defaultProps || {};

    component.defaultProps = Object.assign({}, existingProps, {
        apiConnector: resolve(DI.DiscussApi),
    }) as any;

    return component;
}

export default connectApi;
