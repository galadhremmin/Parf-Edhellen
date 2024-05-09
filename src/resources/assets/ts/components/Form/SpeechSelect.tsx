import { useCallback } from 'react';

import { DI } from '@root/di/keys';
import { excludeProps } from '@root/utilities/func/props';

import { withPropInjection } from '@root/di';
import AsyncSelect from './AsyncSelect/AsyncSelect';
import { IProps } from './SpeechSelect._types';

const InternalProps: (keyof IProps)[] = [ 'apiConnector', 'value' ];

function SpeechSelect(props: IProps) {
    const {
        apiConnector,
        value = null,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() => apiConnector.speeches(), [ apiConnector ]);

    return <AsyncSelect
        {...componentProps}
        loaderOfValues={_getValues}
        value={value}
        textField="name"
        valueField="id"
        valueType="id"
    />;
}

export default withPropInjection(SpeechSelect, {
    apiConnector: DI.SpeechApi,
});
