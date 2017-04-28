import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDWordSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            suggestions: props.suggestions || [],
            value: props.value || null,
            word: ''
        };
    }

    componentWillReceiveProps(props) {
        if (props.suggestions !== undefined) {
            this.setState({
                suggestions: props.suggestions
            });
        }
    }

    /**
     * Sets the word currently selected.
     * @param {Object} value 
     */
    setValue(value) {
        this.setState({
            value
        });
    }

    /**
     * Gets the word currently selected.
     */
    getValue() {
        return this.state.value;
    }

    onWordChange(ev, data) {
        this.setState({
            word: data.newValue
        });

        //this.setValue(data.newValue);
 
        if (typeof this.props.onChange === 'function') {
            this.props.onChange(ev);
        }
    }

    onSuggestionsFetchRequest(data) {
        // already fetching suggestions?
        if (this.loading) {
            return;
        }

        // Throttle search requests, to prevent them from occurring too often.
        if (this.searchDelay) {
            window.clearTimeout(this.searchDelay);
            this.searchDelay = 0;
        }

        const word = data.value;
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
                    suggestions: resp.data
                });
                this.loading = false;
            });

        }, 800);
    }

    onSuggestionsClearRequest() {
        this.setState({
            suggestions: this.props.suggestions === this.state.suggestions
                ? [] : this.props.suggestions
        });
    }

    onSuggestionSelect() {
        console.log(arguments);
    }

    getSuggestionValue(suggestion) {
        return suggestion.word;
    }

    renderSuggestion(suggestion) {
        return <span>{suggestion.word} &mdash; {suggestion.translation}</span>
    }

    render() {
        const inputProps = {
            placeholder: 'Search for a suitable translation',
            value: this.state.word,
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
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    inputProps={inputProps} />
            </div>
        </div>;
    }
}

EDWordSelect.defaultProps = {
    componentName: 'word',
    componentId: undefined,
    value: 0,
    languageId: 0
};

export default EDWordSelect;
