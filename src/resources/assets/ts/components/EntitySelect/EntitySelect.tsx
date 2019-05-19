import classNames from 'classnames';
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

import './EntitySelect.scss';

/**
 * This component does not currently work. It needs to be reinvented.
 * @param props
 */
export default class EntitySelect<T> extends React.Component<IProps<T>, IState> {
    public static defaultProps = {
        formatter: (s) => s || '',
        renderSuggestion: (s) => <span>{JSON.stringify(s)}</span>,
        renderValue: (v) => JSON.stringify(v),
        value: null,
        valueClassNames: '',
    } as Partial<IProps<any>>;

    public state = {
        editing: false,
        focused: false,
        suggestionsFor: null,
        text: '',
    } as IState;

    private _shouldAutofocus = false;

    private _beginRequestSuggestions: (text: string) => void;

    public constructor(props: IProps<T>) {
        super(props);
        this._beginRequestSuggestions = debounce(500, this._requestSuggestions);
    }

    public render() {
        const {
            formatter,
            name,
            renderSuggestion,
            renderValue,
            suggestions,
            value,
            valueClassNames,
        } = this.props;

        const {
            editing,
            focused,
            text,
        } = this.state;

        const {
            _onTextBlur,
            _onTextChange,
            _onTextKeyDown,
            _onValueBlur,
            _onValueChange,
            _onValueFocus,
            _onValueKeyDown,
            _shouldAutofocus,
        } = this;

        if (editing) {
            const inputProps = {
                autoFocus: true,
                name,
                onBlur: _onTextBlur,
                onChange: _onTextChange,
                onKeyDown: _onTextKeyDown,
                value: text,
            };

            return <Autosuggest
                id={`${name}-suggestions`}
                alwaysRenderSuggestions={false}
                focusInputOnSuggestionClick={true}
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
        } else {
            return <label className={classNames(valueClassNames, 'EntitySelect', { 'EntitySelect--focus': focused })}>
                <input
                    autoFocus={_shouldAutofocus}
                    autoCorrect="off"
                    autoComplete="off"
                    checked={true}
                    type="checkbox"
                    name={name}
                    onBlur={_onValueBlur}
                    onFocus={_onValueFocus}
                    onChange={_onValueChange}
                    onKeyDown={_onValueKeyDown}
                    value={JSON.stringify(value)}
                />
                {renderValue(value)}
            </label>;
        }
    }

    private _requestSuggestions(text: string) {
        const {
            onSuggest,
        } = this.props;

        fireEvent(name, onSuggest, text);
    }

    private _applyValue(newValue: T = null) {
        const {
            formatter,
            onChange,
            name,
            suggestions,
            value,
        } = this.props;

        const {
            text,
        } = this.state;

        if (newValue === null && suggestions.length > 0) {
            newValue = suggestions.find(
                (s: T) => formatter(s).toLocaleLowerCase() === text.toLocaleLowerCase(),
            ) || null;

        } else if (formatter(value) === text) {
            newValue = value;
        }

        if (newValue !== value) {
            fireEvent(name, onChange, newValue);
        }

        if (newValue !== null) {
            this._leaveEditMode();
        }
    }

    private _enterEditMode() {
        this._shouldAutofocus = false;
        this.setState({
            editing: true,
            focused: false,
        });
    }

    private _leaveEditMode() {
        this._shouldAutofocus = true;
        this.setState({
            editing: false,
        });
    }

    private _onValueBlur = () => {
        this.setState({
            focused: false,
        });
    }

    private _onValueChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const {
            checked,
        } = ev.target;

        if (! checked) {
            this._enterEditMode();
        }
    }

    private _onValueFocus = () => {
        this.setState({
            focused: true,
        });
    }

    private _onValueKeyDown = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        const code = ev.which;
        if (!(code > 47 && code < 58) && // numeric (0-9)
            !(code > 64 && code < 91) && // upper alpha (A-Z)
            !(code > 96 && code < 123)) { // lower alpha (a-z)
            return;
        }

        ev.preventDefault();
        this._enterEditMode();
    }

    private _onTextBlur = () => {
        this._applyValue();
    }

    private _onTextChange = (ev: React.FormEvent<any>, params: ChangeEvent) => {
        const text = params.newValue;

        this.setState({
            editing: true,
            text,
        });
    }

    private _onTextKeyDown = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        if (ev.which === 13) {
            ev.preventDefault();
            this._applyValue();
        }
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
        this._applyValue(data.suggestion || null);
    }
}
