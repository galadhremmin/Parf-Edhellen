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
        const name = target.name;
        const type = target.nodeName.toUpperCase();
        
        let value = undefined;

        if (type === 'INPUT') {
            switch (target.type.toUpperCase()) {
                case 'CHECKBOX':
                case 'RADIO':
                    value = target.checked ? value || true : undefined;
                    break;
                case 'NUMBER':
                case 'RANGE':
                    value = parseInt(target.value, 10);
                    break;
                default:
                    value = target.value;
            }
        } else if (type === 'SELECT') {
            value = target.options[target.selectedIndex].value;
        } else {
            value = target.value;
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

        this.setState({
            [name]: value
        });
    }
}