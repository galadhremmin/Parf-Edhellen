import React from 'react';
import Autosuggest, {
    ChangeEvent,
    SuggestionSelectedEventData,
} from 'react-autosuggest';

import debounce from '@root/utilities/func/debounce';

import { fireEvent } from '../Component';
import DefaultInput from './DefaultInput';
import {
    IProps,
    IState,
} from './EntitySelect._types';

/**
 * This component does not currently work. It needs to be reinvented.
 * @param props
 */
export default class EntitySelect<T> extends React.Component<IProps<T>, IState> {
    public static defaultProps = {
        formatter: (s) => s || '',
        renderSuggestion: (s) => <span>{JSON.stringify(s)}</span>,
        renderValue: () => null,
        value: null,
    } as Partial<IProps<any>>;

    public state = {
        suggestionsFor: null,
        text: '',
    } as IState;

    private _beginRequestSuggestions: (text: string) => void;

    public constructor(props: IProps<T>) {
        super(props);
        this._beginRequestSuggestions = debounce(500, this._requestSuggestions);
    }

    public render() {
        const {
            formatter,
            loading,
            name,
            renderSuggestion,
            renderValue,
            suggestions,
            value,
        } = this.props;

        const {
            text,
        } = this.state;

        const inputProps = {
            name,
            onChange: this._onChange,
            value: text,
        };

        return <Autosuggest
            id={`${name}-suggestions`}
            alwaysRenderSuggestions={false}
            multiSection={false}
            suggestions={suggestions}
            onSuggestionsClearRequested={this._onSuggestionsClearRequest}
            onSuggestionsFetchRequested={this._onSuggestionsFetchRequest}
            onSuggestionSelected={this._onSuggestionSelected}
            getSuggestionValue={formatter}
            renderInputComponent={DefaultInput}
            renderSuggestion={renderSuggestion}
            inputProps={inputProps}
        />;
    }

    private _onSuggestionsFetchRequest = (ev: Autosuggest.SuggestionsFetchRequestedParams) => {
        const {
            loading,
        } = this.props;

        const {
            suggestionsFor,
        } = this.state;

        const newValue = (ev.value || '').toLocaleLowerCase();

        // already fetching suggestions?
        if (loading || /^\s*$/.test(newValue) || suggestionsFor === newValue) {
            return;
        }

        this.setState({
            suggestionsFor: newValue,
        });

        this._beginRequestSuggestions(newValue);
    }

    private _onSuggestionsClearRequest = () => {
        const {
            onClearSuggestions,
        } = this.props;

        fireEvent(name, onClearSuggestions);
    }

    private _onSuggestionSelected = (ev: React.FormEvent<any>, data: SuggestionSelectedEventData<T>) => {
        const {
            onChange,
        } = this.props;

        fireEvent(name, onChange, data.suggestion || null);
    }

    private _onChange = (ev: React.FormEvent<any>, params: ChangeEvent) => {
        const {
            formatter,
            suggestions,
            onChange,
            name,
        } = this.props;

        const text = params.newValue;
        const suggestion = suggestions.find((s: T) => formatter(s) === text) || null;

        this.setState({
            text,
        });

        if (suggestion !== null) {
            fireEvent(name, onChange, suggestion);
        }
    }

    private _requestSuggestions = (text: string) => {
        const {
            onSuggest,
        } = this.props;

        fireEvent(name, onSuggest, text);
    }
}
