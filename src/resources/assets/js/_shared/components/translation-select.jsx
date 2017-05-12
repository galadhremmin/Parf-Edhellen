import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDTranslationSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            suggestions: props.suggestions || []
        };
    }

    componentWillReceiveProps(props) {
        if (Array.isArray(props.suggestions)) {
            this.setState({
                suggestions: props.suggestions,
                suggestionsFor: undefined
            });
        }
    }

    createStateForValue(value) {
        return {
            value,
            word: value ? value.word : ''
        };
    }

    /**
     * Sets the word currently selected.
     * @param {Object} value - Translation object
     */
    setValue(value) {
        const originalValue = this.state.value;
        this.setState(this.createStateForValue(value));

        if (originalValue !== value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the word currently selected.
     */
    getValue() {
        return this.state.value;
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.state.word;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        const id = this.props.componentId;
        if (! id) {
            return;
        }

        const element = document.getElementById(id);
        if (element) {
            element.focus();
        }
    }

    onWordChange(ev, data) {
        const value = this.state.value && this.state.value.word === data.newValue
                ? this.state.value : undefined;

        this.setState({
            word: data.newValue,
            value
        });

        if (value !== this.state.value) {
            this.triggerChange();
        }
    }

    onSuggestionsFetchRequest(data) {
        const word = (data.value || '').toLocaleLowerCase();

        // already fetching suggestions?
        if (this.loading || /^\s*$/.test(word) || this.state.suggestionsFor === word) {
            return;
        }

        // Throttle search requests, to prevent them from occurring too often.
        if (this.searchDelay) {
            window.clearTimeout(this.searchDelay);
            this.searchDelay = 0;
        }

        this.searchDelay = window.setTimeout(() => {
            this.searchDelay = 0;
            this.loading = true;

            // Retrieve suggestions for the specified word.
            axios.post(EDConfig.api('book/suggest'), {
                words: [ word ], 
                language_id: this.props.languageId,
                inexact: true
            }).then(resp => {
                this.setState({
                    suggestions: resp.data[word] || [],
                    suggestionsFor: word
                });

                this.loading = false;
            });

        }, 800);
    }

    onSuggestionsClearRequest() {
        this.setState({
            suggestions: !Array.isArray(this.props.suggestions) ||
                this.props.suggestions === this.state.suggestions
                ? [] : this.props.suggestions
        });
    }

    onSuggestionSelect(ev, data) {
        ev.preventDefault();
        this.setValue(data.suggestion  || undefined);
    }

    getSuggestionValue(suggestion) {
        return suggestion.word;
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout(() => {
                this.props.onChange({
                    target: this,
                    value: this.getValue()
                });
            }, 0);
        }
    }

    renderInput(inputProps) {
        const valid = !!this.state.value;
        const props = { 
            ...inputProps, 
            className: `form-control ${inputProps.className}`
        };

        return <div className={classNames('input-group', { 'has-warning': !valid && this.props.required, 'has-success': valid })}>
            <input {...props} />
            <div className="input-group-addon">
                <span className={classNames('glyphicon', { 'glyphicon-exclamation-sign': !valid && this.props.required, 'glyphicon-ok': valid })} />
            </div>
        </div>;
    }

    renderSuggestion(suggestion) {
        return <div title={suggestion.comments}>
            <strong>{suggestion.word}</strong>
            {': '}
            {suggestion.type ? <em>{`${suggestion.type} `}</em> : ''}
            {suggestion.translation}
            {' '}
            [{suggestion.source}]<br />
            <small>
                {'by '}
                <em>{suggestion.account_name}</em> 
                {' '}
                {suggestion.translation_group_name 
                    ? <span>(<em>{suggestion.translation_group_name}</em>)</span> : ''}
            </small>
        </div>
    }

    render() {
        const inputProps = {
            placeholder: 'Search for a suitable translation',
            value: this.state.word,
            name: this.props.componentName,
            id: this.props.componentId,
            onChange: this.onWordChange.bind(this)
        };

        return <div>
            <div>
                <Autosuggest 
                    id={`${this.props.componentId || this.props.componentName}-translation-selection`}
                    alwaysRenderSuggestions={false} 
                    multiSection={false}
                    suggestions={this.state.suggestions}
                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequest.bind(this)}
                    onSuggestionsClearRequested={this.onSuggestionsClearRequest.bind(this)}
                    onSuggestionSelected={this.onSuggestionSelect.bind(this)}
                    getSuggestionValue={this.getSuggestionValue.bind(this)}
                    renderInputComponent={this.renderInput.bind(this)}
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    inputProps={inputProps} />
            </div>
        </div>;
    }
}

EDTranslationSelect.defaultProps = {
    componentName: 'word',
    componentId: undefined,
    value: 0,
    languageId: 0,
    required: false
};

export default EDTranslationSelect;
