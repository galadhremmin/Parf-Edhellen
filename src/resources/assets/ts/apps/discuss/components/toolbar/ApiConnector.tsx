import React from 'react';

import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import SharedReference from '@root/utilities/SharedReference';

function connectApi<TProps>(component: React.SFC<TProps>) {
    const existingProps = component.defaultProps || {};

    component.defaultProps = Object.assign({}, existingProps, {
        apiConnector: SharedReference.getInstance(DiscussApiConnector),
    }) as any;

    return component;
}

export default connectApi;
