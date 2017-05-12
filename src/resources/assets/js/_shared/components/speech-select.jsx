import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import EDConfig from 'ed-config';

class EDSpeechSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            typesOfSpeech: []
        };
    }

    componentWillMount() {
        axios.get(EDConfig.api('speech'))
            .then(resp => this.setLoadedTypesOfSpeech(resp.data));
    }

    createStateForValue(value) {
        if (!value) {
            value = 0;
        }

        return {
            value
        };
    }

    setLoadedTypesOfSpeech(typesOfSpeech) {
        this.setState({
          typesOfSpeech
        });
    }

    /**
     * Sets the type of speech currently selected. The object must be retrieved
     * from the server to be considered valid.
     * @param {Object} value 
     */
    setValue(value) {
        const originalValue = this.state.value;
        this.setState(this.createStateForValue(value));

        if (originalValue !== value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the component's current value.
     */
    getValue() {
        return this.state.value;
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.selectInput.options[this.selectInput.selectedIndex].textContent;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        this.selectInput.focus();
    }

    onSpeechChange(ev) {
        this.setValue(parseInt(ev.target.value, 10));
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

    render() {
        const typesOfSpeech = this.state.typesOfSpeech || [];
        return <select onChange={this.onSpeechChange.bind(this)} 
            name={this.props.componentName} 
            id={this.props.componentId}
            value={this.state.value}
            ref={input => this.selectInput = input}
            className={classNames('form-control', { 'disabled': this.state.typesOfSpeech.length < 1 })}>
                <option value={0}></option>
                {this.state.typesOfSpeech.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
        </select>;
    }
}

EDSpeechSelect.defaultProps = {
    componentName: 'speech',
    componentId: undefined,
    value: 0
};

export default EDSpeechSelect;
