import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDWordSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            suggestions: []
        };
    }

    componentWillReceiveProps(props) {
        if (props.value) {
            this.setValue(props.value);
        }
    }

    /**
     * Determines whether the component is in multiple selection state.
     */
    isMultiple() {
        return this.props.multiple;
    }

    /**
     * Determines whether the component is configured to handle senses.
     */
    isSense() {
        return this.props.isSense;
    }

    /**
     * Converts the specified _value_ to a value compatible with local component state.
     * @param {Object} value
     */
    createStateForValue(value) {
        if (! value) {
            value = [];
        } else if (! Array.isArray(value)) {
            value = [ value ];
        }

        if (this.isSense()) {
            value = value.map(v => typeof v.word === 'object' ? v.word : v);
        }

        return { 
            value,
            word: this.isMultiple() ? '' : (value.length > 0 ? value[0].word : '')
        };
    }

    /**
     * Sets the selected inflections. These should be retrieved from the server
     * to be considered valid.
     * 
     * @param {Object[]} inflections 
     */
    setValue(words) {
        const originalValue = this.state.value;
        const state = this.createStateForValue(words);
        const value = state.value;

        if (originalValue.length !== value.length ||
            originalValue.some(v => ! value.some(v0 => v0.id === v.id))) {
            this.setState(state);
            this.triggerChange();
        }
    }

    /**
     * Gets an array containing the inflections currently selected.
     */
    getValue() {
        let values = this.isSense()
            ? this.state.value.map(v => ({
                id: v.id,
                word: v
            })) : this.state.value;

        return this.isMultiple()
            ? values
            : (values.length > 0 ? values[0] : undefined);
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.state.value.map(s => s.word);
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

    getSuggestionValue(suggestion) {
        return suggestion.word;
    }

    renderInput(inputProps) {
        const valid = this.state.value.length > 0;
        const props = { 
            ...inputProps, 
            className: `form-control ${inputProps.className}`
        };

        if (this.isMultiple()) {
            return <div className={classNames({ 'has-warning': !valid && this.props.required, 'has-success': valid })}>
                <input {...props} />
            </div>;
        }

        return <div className={classNames('input-group', { 'has-warning': !valid && this.props.required, 'has-success': valid })}>
            <input {...props} />
            <div className="input-group-addon">
                <span className={classNames('glyphicon', { 'glyphicon-exclamation-sign': !valid && this.props.required, 'glyphicon-ok': valid })} />
            </div>
        </div>;
    }

    renderSuggestion(suggestion) {
        return <span>{suggestion.word}</span>;
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout( () => {
                this.props.onChange({
                    target: this,
                    value: this.getValue()
                });
            }, 0);
        }
    }

    onWordChange(ev, data) {
        this.setState({
            word: data.newValue
        });
    }

    onRemoveWordClick(ev, indexToRemove) {
        this.setValue(this.state.value.filter((v, i) => i !== indexToRemove));
    }

    onSuggestionSelect(ev, data) {
        ev.preventDefault();

        let suggestion = data.suggestion._isNew === true && data.suggestion.hasOwnProperty('_word')
            ? { id: 0, word: data.suggestion._word }
            : data.suggestion;

        const value = this.isMultiple()
            ? [ ...(this.state.value), suggestion ]
            : suggestion;

        this.setValue(value);
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
            axios.post(EDConfig.api('book/word/find'), {
                word,
                max: 10
            }).then(resp => {
                let suggestions = resp.data;
                
                // If the _canCreateNew_ property is set to true, and none of the suggestions is a direct match to the
                // search query, present the author with the option to add a new word.
                if (this.props.canCreateNew && ! suggestions.some(s => s.word === word)) {
                    suggestions = [ { id: 0, word: `Add word "${word}"`, _isNew: true, _word: word } , ...suggestions];
                }

                this.setState({
                    suggestions,
                    suggestionsFor: word
                });

                this.loading = false;
            });

        }, 800);
    }

    onSuggestionsClearRequest() {
        this.setState({
            suggestions: []
        });
    }

    render() {
        const inputProps = {
            placeholder: 'Search for a word.',
            value: this.state.word,
            name: this.props.componentName,
            id: this.props.componentId,
            onChange: this.onWordChange.bind(this)
        };

        return <div className="ed-word-select">
            <div>
                <Autosuggest 
                    id={`${this.props.componentId || this.props.componentName}-word-selection`}
                    alwaysRenderSuggestions={false} // set to _true_ to view all.
                    suggestions={this.state.suggestions}
                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequest.bind(this)}
                    onSuggestionsClearRequested={this.onSuggestionsClearRequest.bind(this)}
                    onSuggestionSelected={this.onSuggestionSelect.bind(this)}
                    getSuggestionValue={this.getSuggestionValue.bind(this)}
                    renderInputComponent={this.renderInput.bind(this)}
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    inputProps={inputProps} />
            </div>
            {this.isMultiple() ? 
            <div>
                {this.state.value.map((w, i) => 
                    <span key={i}>
                        <a className="label label-default selected-word" 
                           onClick={e => this.onRemoveWordClick(e, i)}
                           title={`Press on the label (${w.word}) to remove it.`}>
                           {w.word}
                        </a>
                        {' '}
                    </span>
                )}
            </div> : ''}
        </div>;
    }
}

EDWordSelect.defaultProps = {
    componentName: 'word',
    componentId: undefined,
    value: [],
    multiple: false,
    isSense: false,
    canCreateNew: false,
    required: false
};

export default EDWordSelect;
