import React, {
    useCallback,
    useState,
} from 'react';
import Autosuggest from 'react-autosuggest';

import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';
import SharedReference from '@root/utilities/SharedReference';

import { fireEvent } from '../Component';
import { IProps } from './AccountSelect._types';
import Input from './Input';
import { getSuggestionValue } from './SuggestionFormatter';

function AccountSelect(props: IProps) {
    const {
        apiConnector,
        name,
        onChange,
    } = props;

    const [ loading, setLoading ] = useState(false);
    const [ searchDelay, setSearchDelay ] = useState(0);
    const [ suggestions, setSuggestions ] = useState<IAccountSuggestion[]>(null);
    const [ suggestionsFor, setSuggestionsFor ] = useState(null);

    const onSuggestionsFetchRequest = useCallback((ev: Autosuggest.SuggestionsFetchRequestedParams) => {
        const nickname = (ev.value || '').toLocaleLowerCase();

        // already fetching suggestions?
        if (loading || /^\s*$/.test(nickname) || suggestionsFor === nickname) {
            return;
        }

        // Throttle search requests, to prevent them from occurring too often.
        if (searchDelay) {
            window.clearTimeout(searchDelay);
            setSearchDelay(0);
        }

        setSearchDelay(
            window.setTimeout(async () => {
                setLoading(true);
                setSearchDelay(0);

                const accounts = await apiConnector.find({
                    max: 10,
                    nickname,
                });

                setSuggestions(accounts);
                setSuggestionsFor(nickname);
                setLoading(false);
            }, 800),
        );
    }, [
        apiConnector,
        loading,
        searchDelay,
        setLoading,
        setSearchDelay,
        setSuggestions,
        setSuggestionsFor,
        suggestionsFor,
    ]);

    const onSuggestionsClearRequest = useCallback(() => {
        setSuggestions([]);
    }, [ setSuggestions ]);

    const onSuggestionSelected = useCallback((ev: any, data: Autosuggest.SuggestionSelectedEventData<any>) => {
        fireEvent(name, onChange, data.suggestion || null);
    }, [ name, onChange ]);

    const inputProps: any = {};

    return <Autosuggest
        id={`${name}-account-selection`}
        alwaysRenderSuggestions={false}
        multiSection={false}
        suggestions={suggestions}
        onSuggestionsFetchRequested={onSuggestionsFetchRequest}
        onSuggestionsClearRequested={onSuggestionsClearRequest}
        onSuggestionSelected={onSuggestionSelected}
        getSuggestionValue={getSuggestionValue}
        renderInputComponent={Input}
        renderSuggestion={this.renderSuggestion.bind(this)}
        inputProps={inputProps}
    />;
}

AccountSelect.defaultProps = {
    apiConnector: SharedReference.getInstance(AccountApiConnector),
} as Partial<IProps>;

export default AccountSelect;
