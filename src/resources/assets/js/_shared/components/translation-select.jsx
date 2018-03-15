import React from 'react';
import classNames from 'classnames';
import EDConfig from 'ed-config';

class EDTranslationSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value))
        };
    }

    createStateForValue(value) {
        if (! Array.isArray(value)) {
            if (typeof value === 'string' && value.length > 0) {
                value = [value];
            } else {
                value = [];
            }
        } 

        if (value.length > 0) {
            value = value.map(v => v.hasOwnProperty('translation') ? v : { translation: String(v).trim() });
            this.sortValue(value);
        }

        return {
            value
        };
    }

    /**
     * Sets the type of speech currently selected. The object must be retrieved
     * from the server to be considered valid.
     * @param {Object} value 
     */
    setValue(value) {
        const originalValue = this.state.value;
        const newState = this.createStateForValue(value);
        this.setState(newState);

        const originalTranslations = originalValue.map(v => v.translation);
        const newTranslations = newState.value.map(v => v.translation); 

        const changed = originalTranslations.length !== newTranslations.length || 
            ! originalValue.every((v, i) => newTranslations[i] === v);

        if (changed) {
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
        return this.state.value.map(v => v.translation).join('; ');
    }

    /**
     * Attempts to find the index of the translation belonging with the specified name.
     * @param {String} text 
     */
    getValueForText(name) {
        return this.state.value.findIndex(v => v.translation === name) || -1;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        this.inputField.focus();
    }

    onKeyDown(ev) {
        if (ev.which !== 13) {
            return;
        }

        ev.preventDefault();
        
        this.addTranslation(this.inputField.value);
        this.inputField.value = '';
    }

    onBlur(ev) {
        const value = this.inputField.value;
        if (/^\s*$/.test(value)) {
            return;
        }

        if (confirm(`You have not added “${value}”. Would you like to add it?`)) {
            this.addTranslation(value);
        }

        this.inputField.value = '';
    }

    onRemoveTranslationClick(ev, i) {
        if (i < 0 || i >= this.state.value.length) {
            return;
        }

        const value = this.state.value;
        value.splice(i, 1);
        this.setValue(value);
    }

    addTranslation(translation) {
        const value = this.getValue();

        const existingPosition = value.findIndex(
            v => v.translation.toLocaleLowerCase() === translation.toLocaleLowerCase()
        );
        if (existingPosition > -1) {
            return;
        }

        this.setValue([...value, translation]);
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

    sortValue(value) {
        value.sort(function (a, b) {
            return a.translation.localeCompare(b.translation);
        });
    }

    render() {
        return <div className="ed-word-select">
            <input type="text" name={this.props.componentName} id={this.props.componentId || undefined} 
                placeholder="Type your gloss here. Press the enter-key to confirm."
                className="form-control" ref={c => this.inputField = c} 
                onKeyDown={this.onKeyDown.bind(this)} onBlur={this.onBlur.bind(this)} />
            <div>
                {this.state.value.map((v, i) => 
                    <span key={i}>
                        <a className="label label-default selected-word" 
                           onClick={e => this.onRemoveTranslationClick(e, i)}
                           title={`Press on the label (${v.translation}) to remove it.`}>
                           {v.translation}
                        </a>
                        {' '}
                    </span>
                )}
            </div> 
        </div>
    }
}

EDTranslationSelect.defaultProps = {
    componentName: 'translations',
    componentId: undefined,
    value: 0
};

export default EDTranslationSelect;
