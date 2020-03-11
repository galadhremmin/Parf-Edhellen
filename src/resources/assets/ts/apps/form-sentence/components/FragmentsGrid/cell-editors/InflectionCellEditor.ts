import {
    Constants,
    ICellEditorComp,
    ICellEditorParams,
    PopupComponent,
} from '@ag-grid-community/all-modules';

import { ISentenceFragmentInflection } from '@root/connectors/backend/IBookApi';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import {
    IFragmentGridMetadata,
} from '../FragmentsGrid._types';

export default class InflectionCellEditor extends PopupComponent implements ICellEditorComp {
    private static TEMPLATE = `<div class="ag-input-wrapper" role="presentation">
        <span class="ag-input-selected-values"></span>
        <input type="text" list="ag-input-available-values" />
        <datalist id="ag-input-available-values"></datalist>
    </div>`;

    /**
     * Determines whether the text field should be in focus after the cell editor has mounted.
     */
    private focusAfterAttached: boolean;
    private _editorParams: ICellEditorParams;

    private _inputElement: HTMLInputElement;
    private _dataListElement: HTMLDataListElement;
    private _valuesWrapper: HTMLSpanElement;
    private _values: ISentenceFragmentInflection[];

    private get _groupedInflections() {
        return (this._editorParams as IFragmentGridMetadata).groupedInflections;
    }

    private get _inflections() {
        return (this._editorParams as IFragmentGridMetadata).inflections;
    }

    constructor() {
        super(InflectionCellEditor.TEMPLATE);
    }

    public init(params: ICellEditorParams): void {
        const inputElement = this.getGui().querySelector<HTMLInputElement>('input');
        const dataListElement = this.getGui().querySelector<HTMLDataListElement>('datalist');
        const valuesWrapper = this.getGui().querySelector<HTMLSpanElement>('span.ag-input-selected-values');

        this._inputElement = inputElement;
        this._dataListElement = dataListElement;
        this._valuesWrapper = valuesWrapper;

        this._editorParams  = params;

        let values = params.value;

        if (params.cellStartedEdit) {
            this.focusAfterAttached = true;

            if (Constants.KEY_BACKSPACE === params.keyPress) {
                // The customer is interacting with the grid via the keyboard, and they pressed
                // the backspace key -- the expected behavior is to remove the last value from
                // the list of values.
                values = values.slice(0, values.length - 1);

            } else if (Constants.KEY_DELETE === params.keyPress) {
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


        this.addDestroyableEventListener(this._inputElement, 'keydown', this._onKeyDown);
    }

    public afterGuiAttached() {
        if (this.focusAfterAttached) {
            this._inputElement.focus();
        }
    }

    public focusIn() {
        this._inputElement.focus();
    }

    public getValue() {
        return this._values;
    }

    public isPopup() {
        return true;
    }

    private _updateValues() {
        const wrapper = this._valuesWrapper;
        const inflections = this._inflections;
        const labels: string[] = [];
        const valueMap = new Set<number>();

        this._values.forEach((value) => {
            valueMap.add(value.inflectionId);

            if (! inflections.has(value.inflectionId)) {
                return;
            }

            labels.push(`<span class="label label-default">${inflections.get(value.inflectionId).name}</span>`);
        });

        wrapper.innerHTML = labels.join('');

        // This process is oddly expensive, so kick it off in its own process.
        window.setTimeout(() => this._updateDataList(valueMap), 0);
    }

    private _addValue(inflectionName: string) {
        const inflections = this._inflections;
        let selectedInflection: IInflection = null;
        for (const inflection of inflections.values()) {
            if (inflection.name === inflectionName) {
                selectedInflection = inflection;
                break;
            }
        }

        const values = this._values;
        if (selectedInflection !== null && //
            ! values.some(i => i.inflectionId === selectedInflection.id)) {
            values.push({
                inflectionId: selectedInflection.id,
            });

            this._updateValues();
            this._inputElement.value = '';
        }
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
        const groupedInflections = this._groupedInflections;
        const options: string[] = [];

        Object.keys(groupedInflections).forEach((group: string) => {
            groupedInflections[group].forEach((inflection) => {
                if (! values.has(inflection.id)) {
                    options.push(`<option value="${inflection.name}">${group}</option>`);
                }
            });
        });

        this._dataListElement.innerHTML = options.join('');
    }

    private _onKeyDown = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        switch (event.keyCode) {
            case Constants.KEY_ENTER:
                if (target.value.length > 0) {
                    event.stopPropagation();
                    this._addValue(target.value);
                }
                break;
            case Constants.KEY_BACKSPACE:
                if (target.value.length === 0) {
                    event.stopPropagation();
                    this._removeLastValue();
                }
                break;
        }
    }
}