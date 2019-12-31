import React, { useCallback } from 'react';

import { DI, resolve } from '@root/di';
import { excludeProps } from '@root/utilities/func/props';

import AsyncSelect from './AsyncSelect/AsyncSelect';
import { IProps } from './GlossGroupSelect._types';

const InternalProps: Array<keyof IProps> = [ 'apiConnector' ];

function GlossGroupSelect(props: IProps) {
    const {
        apiConnector,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() => apiConnector.groups(), [ apiConnector ]);

    return <AsyncSelect
        {...componentProps}
        allowEmpty={true}
        loaderOfValues={_getValues}
        textField="name"
        valueField="id"
        valueType="id"
    />;
}

GlossGroupSelect.defaultProps = {
    apiConnector: resolve(DI.BookApi),
    value: null,
} as IProps;

export default GlossGroupSelect;