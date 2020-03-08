import {
    ICellRendererComp,
    ICellRendererParams,
} from '@ag-grid-community/all-modules';
import { IAugmentedCellRendererParams } from '../FragmentsGrid._types';

class SpeechRenderer implements ICellRendererComp {
    private _cell: HTMLSelectElement;

    public init(params: ICellRendererParams) {
        const cell = document.createElement('select');
        (params as IAugmentedCellRendererParams).speeches.forEach((speech) => {
            cell.options[cell.options.length] = new Option(speech.name, speech.id.toString(10));
        });

        this._cell = cell;

        this.refresh(params);
    }

    public getGui() {
        return this._cell;
    }

    public refresh(params: ICellRendererParams) {
        this._cell.value = params.valueFormatted || params.value;
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}

export default SpeechRenderer;
