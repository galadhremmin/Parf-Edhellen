import { useCallback } from 'react';

import { withPropResolving } from '@root/di';
import { DI } from '@root/di/keys';
import { excludeProps } from '@root/utilities/func/props';

import AsyncSelect from './AsyncSelect/AsyncSelect';
import { IProps } from './GlossGroupSelect._types';

const InternalProps: (keyof IProps)[] = [ 'apiConnector' ];

function GlossGroupSelect(props: IProps) {
    const {
        apiConnector,
    } = props;

    const componentProps = excludeProps(props, InternalProps);

    const _getValues = useCallback(() => apiConnector.groups(), [ apiConnector ]);

    return <AsyncSelect
        {...componentProps}
        loaderOfValues={_getValues}
        textField="name"
        valueField="id"
        valueType="id"
    />;
}

GlossGroupSelect.defaultProps = {
    allowEmpty: true,
    value: null,
} as IProps;

export default withPropResolving(GlossGroupSelect, {
    apiConnector: DI.BookApi,
});
