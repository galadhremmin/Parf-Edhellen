import {
    ICellRendererComp,
    ICellRendererParams,
} from 'ag-grid-community';
import { IAugmentedCellRendererParams } from '../cell-editors/InflectionCellEditor._types';

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
        const value = params.value;

        this._cell.textContent = speeches.has(value) //
            ? speeches.get(value).name : '-';
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}

