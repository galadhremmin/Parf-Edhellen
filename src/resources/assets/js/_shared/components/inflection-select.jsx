import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDInflectionSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            inflections: [],
            selectedInflections: [],
            groupNames: [],
            value: props.value || '',
            suggestions: []
        };
    }

    componentWillMount() {
        axios.get(EDConfig.api('inflection'))
            .then(resp => this.setLoadedInflections(resp.data));
    }

    setLoadedInflections(inflections) {
        const groupNames = Object.keys(inflections);

        groupNames.forEach(groupName => {
            inflections[groupName].forEach(inflection => {
                inflection.name = inflection.name.toLocaleLowerCase();
            });
        });

        this.setState({
          inflections,
          groupNames
        });
    }

    /**
     * Sets the selected inflections. These should be retrieved from the server
     * to be considered valid.
     * 
     * @param {Object[]} inflections 
     */
    setValue(selectedInflections) {
        this.setState({
            selectedInflections,
            value: ''
        });
    }

    /**
     * Gets an array containing the inflections currently selected.
     */
    getValue() {
        return this.state.selectedInflections || [];
    }

    getSuggestions(data) {
        const name = data.value.toLocaleLowerCase();

        let sections = [];
        for (let groupName of this.state.groupNames) {
            const inflections = this.state.inflections[groupName].filter(
                i => i.name.length >= name.length && i.name.substr(0, name.length) === name &&
                    this.state.selectedInflections.indexOf(i) === -1 // isn't selected!
            );

            if (inflections.length > 0) {
                sections.push({
                    inflections,
                    groupName
                });
            }
        }

        return sections;
    }

    getSuggestionValue(suggestion) {
        return suggestion.name;
    }

    getSectionSuggestions(section) {
        return section.inflections;
    }

    renderSuggestion(suggestion) {
        return <span>{suggestion.name}</span>;
    }

    renderSectionTitle(section) {
        return (
            <strong>{section.groupName}</strong>
        );
    }

    onInflectionChange(ev, data) {
        this.setState({
            value: data.newValue
        });
    }

    onRemoveInflectionClick(ev, inflection) {
        this.setState({
            selectedInflections: this.state.selectedInflections.filter(i => i.id !== inflection.id)
        });
    }

    onSuggestionSelect(ev, data) {
        ev.preventDefault();
        this.setValue([ ...this.state.selectedInflections, data.suggestion ]);
    }

    onSuggestionsFetchRequest(data) {
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
            placeholder: 'Search for an inflection',
            value: this.state.value,
            name: this.props.componentName,
            id: this.props.componentId,
            onChange: this.onInflectionChange.bind(this)
        };

        return <div className="ed-inflection-select">
            <div>
                <Autosuggest 
                    alwaysRenderSuggestions={false} // set to _true_ to view all.
                    multiSection={true}
                    suggestions={this.state.suggestions}
                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequest.bind(this)}
                    onSuggestionsClearRequested={this.onSuggestionsClearRequest.bind(this)}
                    onSuggestionSelected={this.onSuggestionSelect.bind(this)}
                    getSuggestionValue={this.getSuggestionValue.bind(this)}
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    renderSectionTitle={this.renderSectionTitle.bind(this)}
                    getSectionSuggestions={this.getSectionSuggestions.bind(this)}
                    inputProps={inputProps} />
            </div>
            <div>
                {this.state.selectedInflections.map(i => 
                    <span key={i.id}>
                        <a className="label label-default selected-inflection" 
                           onClick={e => this.onRemoveInflectionClick(e, i)}
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

EDInflectionSelect.defaultProps = {
    componentName: 'inflection',
    componentId: undefined,
    value: 0
};

export default EDInflectionSelect;
