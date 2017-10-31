import React from 'react';

/**
 * Provides functionality to sync form components with component state.
 */
export class EDStatefulFormComponent extends React.Component {

    /**
     * Handles form components' onChange-event.
     * @param {Event} ev 
     * @param {number|date|undefined} dataType 
     */
    onChange(ev, dataType) {
        const target = ev.target;

        let name, value;
        if (target instanceof React.Component) {
            // a react component trigering the onChange event
            ({ name, value } = this.onReactChange(ev, target));
        } else {
            ({ name, value } = this.onInputChange(ev, target));
        }

        if (value === undefined) {
            return;
        }

        if (dataType !== undefined) {
            switch (dataType) {
                case 'number':
                    value = parseInt(value, 10);
                    break;
                case 'date':
                    value = Date.parse(value);
                    break;
            }
        }

        // look for dots in the name. If they exists, the name is actually a path:
        if (name.indexOf('.') > -1) {
            const parts = name.split('.');
            name = parts[0];
            
            let ptr = this.state[name] || {};
            for (let i = 1; i < parts.length - 1; i += 1) {
                ptr[parts[i]] = ptr[parts[i]] || {};

                ptr = ptr[parts[i]];
            }

            ptr[parts[parts.length - 1]] = value;
            value = ptr;
        } 

        this.setState({
            [name]: value
        });
    }

    /**
     * Transforms a react component event into an object containing two properties: 
     * name of component (_name_) and component's current value (_value_). The method 
     * expects the react component to implement _getValue_.
     * @param {Event} ev - component event object.
     * @param {Component} target - React component triggering the event (ev.target).
     */
    onReactChange(ev, target) {
        return { 
            name: target.props.componentName || target.props.componentId, 
            value: ev.hasOwnProperty('value') ? ev.value : target.getValue() 
        };
    }
    
    /**
     * Transforms an input change event into an object containing two properties:
     * name of input element (_name_) and the input element's current value (_value_).
     * @param {Event} ev - input event object.
     * @param {Component} target - input element.
     */
    onInputChange(ev, target) {
        const type = target.nodeName.toUpperCase();

        let name = target.name;
        let value = undefined;

        if (type === 'INPUT') {

            value = target.value;

            if (/^(on|off|true|false)$/i.test(value)) {
                value = true;
            } else if (/^[0-9]+$/.test(value)) {
                value = parseInt(value, 10);
            }

            if (/^checkbox|radio$/i.test(target.type)) {
                value = target.checked ? value : ((value === true) ? false : null);
            }

        } else if (type === 'SELECT') {
            value = target.options[target.selectedIndex].value;
        } else {
            value = target.value;
        }

        return { name, value }; 
    }
}
