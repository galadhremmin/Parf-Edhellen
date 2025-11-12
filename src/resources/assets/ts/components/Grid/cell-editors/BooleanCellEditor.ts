import type {
    ICellEditorComp,
    ICellEditorParams,
} from '@ag-grid-community/core';

export default class BooleanCellEditor implements ICellEditorComp {
    private _value: boolean;
    private _label: HTMLLabelElement;
    private _checkboxInput: HTMLInputElement;

    init(params: ICellEditorParams) {
        this._value = params.value;

        const input = this._checkboxInput = document.createElement('input');
        input.id = `ed-BooleanCellEditor-checkbox-${params.rowIndex}`;
        input.className = 'me-2';
        input.type = 'checkbox';
        input.value = '1';

        // deliberately convert to boolean, should the data type not match at runtime:
        input.checked = typeof params.value === 'boolean' ? params.value : !! params.value;
 
        input.addEventListener('change', (ev: Event) => {
            this._value = (ev.target as HTMLInputElement).checked;
        });

        const label = this._label = document.createElement('label');
        label.style.display = 'block';
        label.className = 'text-center';
        label.appendChild(input);
        label.appendChild(document.createTextNode('Yes'));
    }
 
    /* Component Editor Lifecycle methods */
    // gets called once when grid ready to insert the element
    getGui() {
        return this._label;
    }
 
    // the final value to send to the grid, on completion of editing
    getValue() {
        // this simple editor doubles any value entered into the input
        return this._value;
    }
 
    // after this component has been created and inserted into the grid
    afterGuiAttached() {
        this._checkboxInput.focus();
    }
}
