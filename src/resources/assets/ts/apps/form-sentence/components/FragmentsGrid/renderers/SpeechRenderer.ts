import {
    ICellRendererComp,
    ICellRendererParams,
} from '@ag-grid-community/all-modules';
import { IAugmentedCellRendererParams } from '../FragmentsGrid._types';

export default class SpeechRenderer implements ICellRendererComp {
    private _cell: HTMLDivElement;

    public init(params: ICellRendererParams) {
        const cell = document.createElement('div');
        this._cell = cell;

        this.refresh(params);
    }

    public getGui() {
        return this._cell;
    }

    public refresh(params: ICellRendererParams) {
        const speeches = (params as IAugmentedCellRendererParams).speeches;
        const value = parseInt(params.value, 10);

        this._cell.textContent = speeches.has(value) //
            ? speeches.get(value).name : 'invalid';
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}

