import React, { useCallback } from 'react';

import { DI, resolve } from '@root/di';
import { excludeProps } from '@root/utilities/func/props';

import AsyncSelect from './AsyncSelect/AsyncSelect';
import { IProps } from './SpeechSelect._types';

const InternalProps: Array<keyof IProps> = [ 'apiConnector' ];

function SpeechSelect(props: IProps) {
    const {
        apiConnector,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() => apiConnector.speeches(), [ apiConnector ]);

    return <AsyncSelect
        {...componentProps}
        loaderOfValues={_getValues}
        textField="name"
        valueField="id"
        valueType="id"
    />;
}

SpeechSelect.defaultProps = {
    apiConnector: resolve(DI.SpeechApi),
    value: null,
} as IProps;

export default SpeechSelect;
