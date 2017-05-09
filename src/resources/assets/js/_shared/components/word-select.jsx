import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDWordSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            value: props.value || [],
            word: '',
            suggestions: []
        };
    }

    /**
     * Sets the selected inflections. These should be retrieved from the server
     * to be considered valid.
     * 
     * @param {Object[]} inflections 
     */
    setValue(words) {
        this.setState({
            word: '',
            value: Array.isArray(words) ? words : [words]
        });

        this.triggerChange();
    }

    /**
     * Gets an array containing the inflections currently selected.
     */
    getValue() {
        return this.state.value || [];
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.state.value.map(i => i.word);
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
        return suggestion.id;
    }

    renderSuggestion(suggestion) {
        return <span>{suggestion.word}</span>;
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout( () => {
                this.props.onChange({
                    target: this,
                    value: this.state.value
                });
            }, 0);
        }
    }

    onWordChange(ev, data) {
        this.setState({
            value: data.newValue
        });
    }

    onRemoveWordClick(ev, word) {
        this.setValue(this.state.value.filter(i => i.id !== word.id));
    }

    onSuggestionSelect(ev, data) {
        ev.preventDefault();
        this.setValue([ ...this.state.value, data.suggestion ]);
    }

    onSuggestionsFetchRequest(data) {
        // TODO!

        this.setState({
            suggestions: this.getSuggestions(data)
        });
    }

    onSuggestionsClearRequest() {
        this.setState({
            suggestions: []
        });
    }

    render() {
        const inputProps = {
            placeholder: 'Search for a word',
            value: this.state.value,
            name: this.props.componentName,
            id: this.props.componentId,
            onChange: this.onWordChange.bind(this)
        };

        return <div className="ed-word-select">
            <div>
                <Autosuggest 
                    alwaysRenderSuggestions={false} // set to _true_ to view all.
                    suggestions={this.state.suggestions}
                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequest.bind(this)}
                    onSuggestionsClearRequested={this.onSuggestionsClearRequest.bind(this)}
                    onSuggestionSelected={this.onSuggestionSelect.bind(this)}
                    getSuggestionValue={this.getSuggestionValue.bind(this)}
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    inputProps={inputProps} />
            </div>
            <div>
                {this.state.selectedInflections.map(i => 
                    <span key={i.id}>
                        <a className="label label-default selected-inflection" 
                           onClick={e => this.onRemoveWordClick(e, i)}
                           title={`Press on the label (${i.name}) to remove it.`}>
                           {i.name}
                        </a>
                        {' '}
                    </span>
                )}
            </div>
        </div>;
    }
}

EDWordSelect.defaultProps = {
    componentName: 'word',
    componentId: undefined,
    value: []
};

export default EDWordSelect;
