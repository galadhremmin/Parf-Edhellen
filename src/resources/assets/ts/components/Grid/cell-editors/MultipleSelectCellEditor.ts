import {
    ICellEditorComp,
    ICellEditorParams,
    PopupComponent,
} from '@ag-grid-community/core';

export default class MultipleSelectCellEditor<T, V = T> extends PopupComponent implements ICellEditorComp {
    private static TEMPLATE = `<div class="ag-input-wrapper" role="presentation">
        <span class="ag-input-selected-values"></span>
        <input type="text" list="ag-input-available-values" />
        <datalist id="ag-input-available-values"></datalist>
    </div>`;

    /**
     * Determines whether the text field should be in focus after the cell editor has mounted.
     */
    private focusAfterAttached: boolean;

    private _inputElement: HTMLInputElement;
    private _dataListElement: HTMLDataListElement;
    private _valuesWrapper: HTMLSpanElement;
    private _values: V[];

    protected get allOptions(): Map<string, T[]> | Set<T> {
        return new Map();
    }

    protected get maxNumberOfValues() {
        return Number.MAX_VALUE;
    }

    constructor() {
        super(MultipleSelectCellEditor.TEMPLATE);
    }

    public init(params: ICellEditorParams): void {
        const inputElement = this.getGui().querySelector<HTMLInputElement>('input');
        const dataListElement = this.getGui().querySelector<HTMLDataListElement>('datalist');
        const valuesWrapper = this.getGui().querySelector<HTMLSpanElement>('span.ag-input-selected-values');

        this._inputElement = inputElement;
        this._dataListElement = dataListElement;
        this._valuesWrapper = valuesWrapper;

        let values = Array.isArray(params.value) ? [...params.value] : [params.value];

        if (params.cellStartedEdit) {
            this.focusAfterAttached = true;

            if ('Backspace' === params.eventKey) {
                // The customer is interacting with the grid via the keyboard, and they pressed
                // the backspace key -- the expected behavior is to remove the last value from
                // the list of values.
                values = values.slice(0, values.length - 1);

            } else if ('Delete' === params.eventKey) {
                // The customer is pressing the delete key -- the expected behavior is that all
                // current values are removed.
                values = [];

            } else if (params.charPress) {
                this._inputElement.value = params.charPress;
            }

        } else {
            this.focusAfterAttached = false;
        }

        this._values = values;
        this._updateValues();

        this.addManagedListener(this._inputElement, 'keydown', this._onKeyDown);
    }

    public getValue() {
        const values = this._values;
        if (this.maxNumberOfValues === 1) {
            return values.length === 0 ? 0 : values[0];
        } else {
            return values;
        }
    }

    public afterGuiAttached() {
        if (this.focusAfterAttached) {
            this._inputElement.focus();
        }
    }

    public focusIn() {
        this._inputElement.focus();
    }

    public isPopup() {
        return true;
    }

    protected getOptionId(option: T) {
        return 0;
    }

    protected getOptionText(option: T) {
        return '';
    }

    protected getValueId(value: V) {
        return 0;
    }

    protected getValueText(value: V) {
        return '';
    }

    protected convertOptionToValue(option: T): V {
        return option as any; // infer it the option is the same as the value type.
    }

    private _updateValues() {
        const wrapper = this._valuesWrapper;
        const labels: string[] = [];
        const valueMap = new Set<number>();

        this._values.forEach((value) => {
            const valueId = this.getValueId(value);
            const valueText = this.getValueText(value);
            valueMap.add(valueId);
            labels.push(`<span class="badge bg-secondary">${valueText}</span>`);
        });

        wrapper.innerHTML = labels.join('');

        // This process is oddly expensive, so kick it off in its own process.
        window.setTimeout(() => this._updateDataList(valueMap), 0);
    }

    private _checkEquality(option: T, valueText: string) {
        if (this.getOptionText(option) === valueText) {
            return this.convertOptionToValue(option);
        }

        return null;
    }

    private _addValue(valueText: string) {
        const {
            _values: values,
            allOptions,
            maxNumberOfValues,
        } = this;

        let selectedValue: V = null;

        if (allOptions instanceof Map) {
            for (const options of allOptions.values()) {
                for (const option of options) {
                    selectedValue = this._checkEquality(option, valueText);
                    if (selectedValue !== null) {
                        break;
                    }
                }
                if (selectedValue !== null) {
                    break;
                }
            }
        } else {
            for (const option of allOptions.values()) {
                selectedValue = this._checkEquality(option, valueText);
                if (selectedValue !== null) {
                    break;
                }
            }
        }

        if (selectedValue !== null && //
            ! values.some(i => this.getValueId(i) === this.getValueId(selectedValue))) {
            if (values.length >= maxNumberOfValues) {
                values.splice(values.length - 1, 1, selectedValue);
            } else {
                values.push(selectedValue);
            }
            this._updateValues();
            this._inputElement.value = '';

            return true;
        }

        return false;
    }

    private _removeLastValue() {
        const values = this._values;
        if (values.length === 0) {
            return;
        }

        values.splice(values.length - 1, 1);
        this._updateValues();
    }

    private _updateDataList(values: Set<number>) {
        const allOptions = this.allOptions;
        const html: string[] = [];

        if (allOptions instanceof Map) {
            for (const group of allOptions.keys()) {
                for (const option of allOptions.get(group)) {
                    if (! values.has(this.getOptionId(option))) {
                        html.push(`<option value="${this.getOptionText(option)}">${group}</option>`);
                    }
                }
            }
        } else {
            for (const option of allOptions) {
                if (! values.has(this.getOptionId(option))) {
                    html.push(`<option value="${this.getOptionText(option)}"></option>`);
                }
            }
        }

        this._dataListElement.innerHTML = html.join('');
    }

    private _onKeyDown = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        const {
            _values: values,
            maxNumberOfValues,
        } = this;

        switch (event.key) {
            case 'Enter':
                if (target.value.length > 0) {
                    if (this._addValue(target.value) && maxNumberOfValues !== values.length) {
                        event.preventDefault();
                    }
                }
                break;
            case 'Backspace':
                if (target.value.length === 0) {
                    event.preventDefault();
                    this._removeLastValue();
                }
                break;
        }
    }
}
