import React, { useCallback } from 'react';

import SpeechResourceApiConnector from '@root/connectors/backend/SpeechResourceApiConnector';
import { excludeProps } from '@root/utilities/func/props';
import SharedReference from '@root/utilities/SharedReference';

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
    apiConnector: SharedReference.getInstance(SpeechResourceApiConnector),
    value: null,
} as IProps;

export default SpeechSelect;
