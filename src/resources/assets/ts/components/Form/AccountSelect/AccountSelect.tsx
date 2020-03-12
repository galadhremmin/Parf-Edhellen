import React, {
    useCallback,
    useState,
} from 'react';

import { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';
import { DI, resolve } from '@root/di';

import { fireEvent } from '../../Component';
import { IComponentEvent } from '../../Component._types';
import EntitySelect from '../EntitySelect';
import { IProps } from './AccountSelect._types';
import AccountSuggestion from './AccountSuggestion';
import { injectAccountValue } from './AccountValue';

import './AccountSelect.scss';

const accountFormatter = (account: IAccountSuggestion) => account ? account.nickname : '';

function AccountSelect(props: IProps) {
    const [ suggestions, setSuggestions ] = useState([]);

    const {
        apiConnector,
        name,
        onChange,
        required,
        value,
    } = props;

    const _onClearSuggestions = useCallback(() => {
        setSuggestions([]);
    }, [ setSuggestions ]);

    const _onSuggest = useCallback(async (ev: IComponentEvent<string>) => {
        const newSuggestions = await apiConnector.find({
            max: 15,
            nickname: ev.value,
        });

        setSuggestions(newSuggestions);
    }, [ apiConnector ]);

    const _onChange = useCallback((ev: IComponentEvent<IAccountSuggestion>) => {
        fireEvent(name, onChange, ev.value);
    }, [ name, onChange ]);

    return <EntitySelect<IAccountSuggestion>
        formatter={accountFormatter}
        name={name}
        onChange={_onChange}
        onClearSuggestions={_onClearSuggestions}
        onSuggest={_onSuggest}
        required={required || false}
        renderSuggestion={AccountSuggestion}
        renderValue={injectAccountValue}
        suggestions={suggestions}
        value={value}
        valueClassNames="AccountSelect--value"
    />;
}

AccountSelect.defaultProps = {
    apiConnector: resolve(DI.AccountApi),
} as Partial<IProps>;

export default AccountSelect;
