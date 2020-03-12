import {
    Constants,
    ICellEditorComp,
    ICellEditorParams,
    PopupComponent,
} from '@ag-grid-community/all-modules';

export default class GlossCellEditor extends PopupComponent implements ICellEditorComp {
    private static TEMPLATE = `<div class="ag-input-wrapper" role="presentation">
        <input type="text" list="ag-input-available-values" />
        <datalist id="ag-input-available-values"></datalist>
    </div>`;

    /**
     * Determines whether the text field should be in focus after the cell editor has mounted.
     */
    private focusAfterAttached: boolean;
    private _value: number;

    private _inputElement: HTMLInputElement;

    constructor() {
        super(GlossCellEditor.TEMPLATE);
    }

    public init(params: ICellEditorParams): void {
        const inputElement = this.getGui().querySelector<HTMLInputElement>('input');
        this._inputElement = inputElement;

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

        this._value = values;

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
        return this._value;
    }

    public isPopup() {
        return true;
    }

    private _onKeyDown = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        switch (event.keyCode) {
            case Constants.KEY_ENTER:
                if (target.value.length > 0) {
                    event.stopPropagation();
                }
                break;
            case Constants.KEY_BACKSPACE:
                if (target.value.length === 0) {
                    event.stopPropagation();
                }
                break;
        }

        console.log(event);
    }
}