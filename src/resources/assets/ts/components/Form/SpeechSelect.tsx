import { useCallback } from 'react';

import { DI } from '@root/di/keys';
import { excludeProps } from '@root/utilities/func/props';
import Cache from '@root/utilities/Cache';

import { withPropInjection } from '@root/di';
import AsyncSelect from './AsyncSelect/AsyncSelect';
import type { IProps } from './SpeechSelect._types';

const InternalProps: (keyof IProps)[] = [ 'apiConnector', 'value' ];

function SpeechSelect(props: IProps) {
    const {
        apiConnector,
        value = null,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() =>
        Cache.withTransientStorage(() => apiConnector.speeches(), 'ed.glossary.speeches').get(), [ apiConnector ]);

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
