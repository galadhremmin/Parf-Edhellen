import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDTranslationSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            suggestions: props.suggestions || [],
            value: props.value || undefined,
            word: ''
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

    /**
     * Sets the word currently selected.
     * @param {Object} value - Translation object
     */
    setValue(value) {
        this.setState({
            value,
            word: value ? value.word : ''
        });
    }

    /**
     * Gets the word currently selected.
     */
    getValue() {
        return this.state.value;
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
        this.setState({
            word: data.newValue,
            value: this.state.value && this.state.value.word === data.newValue
                ? this.state.value : undefined
        });

        //this.setValue(data.newValue);
 
        if (typeof this.props.onChange === 'function') {
            this.props.onChange(ev);
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
        this.setState({
            value: data.suggestion || undefined
        })
    }

    getSuggestionValue(suggestion) {
        return suggestion.word;
    }

    renderInput(inputProps) {
        const valid = !!this.state.value;
        return <div className={classNames('input-group', { 'has-warning': !valid, 'has-success': valid })}>
            <input {...inputProps} />
            <div className="input-group-addon">
                <span className={classNames('glyphicon', { 'glyphicon-ok': valid, 'glyphicon-exclamation-sign': !valid })} />
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
    languageId: 0
};

export default EDTranslationSelect;
