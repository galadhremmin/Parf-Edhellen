import { useCallback } from 'react';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { excludeProps } from '@root/utilities/func/props';

import AsyncSelect from './AsyncSelect/AsyncSelect';
import { IProps } from './GlossGroupSelect._types';

const InternalProps: (keyof IProps)[] = [ 'apiConnector', 'allowEmpty', 'value' ];

function GlossGroupSelect(props: IProps) {
    const {
        apiConnector,
        allowEmpty = true,
        value = null,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() => apiConnector.groups(), [ apiConnector ]);

    return <AsyncSelect
        {...componentProps}
        loaderOfValues={_getValues}
        allowEmpty={allowEmpty}
        value={value}
        textField="name"
        valueField="id"
        valueType="id"
    />;
}

export default withPropInjection(GlossGroupSelect, {
    apiConnector: DI.BookApi,
});
