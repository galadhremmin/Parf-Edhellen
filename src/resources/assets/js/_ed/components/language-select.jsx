import React from 'react';
import EDConfig from 'ed-config';

class EDLanguageSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            languages: EDConfig.languages(),
        }
    }

    componentWillReceiveProps(props) {
        if (props.value) {
            this.setValue(props.value);
        }
    }

    createStateForValue(value) {
        if (! value) {
            value = 0;
        }

        return {
            value
        };
    }

    /**
     * Sets the language currently selected.
     * @param {number} languageId - language ID
     */
    setValue(languageId) {
        const originalValue = this.state.value;
        const state = this.createStateForValue(languageId);
        this.setState(state);
        
        if (originalValue !== state.value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the id for the language currently selected.
     */
    getValue() {
        return this.state.value;
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return null;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        this.selectInput.focus();
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

    onLanguageChange(ev) {
        this.setState({
            value: ev.target.value
        })
        this.triggerChange();
    }

    render() {
        return <select className={this.props.className || undefined} value={this.state.value} 
            id={this.props.componentId || undefined} name={this.props.componentName || undefined}
            onChange={this.onLanguageChange.bind(this)} ref={input => this.selectInput = input}>
            <option value={0}>All languages</option>
            {Object.keys(this.state.languages).map((category, i) => 
            <optgroup label={category} key={i}>
            {this.state.languages[category].map(l => 
                <option value={l.id} key={l.id}>{l.name}</option>
            )}
            </optgroup>
            )}
        </select>;
    }
}

EDLanguageSelect.defaultProps = {
    value: 0,
    componentName: 'language',
    componentId: 'language',
    className: undefined
};

export default EDLanguageSelect;
