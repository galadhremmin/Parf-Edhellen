import {
    ICellRendererComp,
    ICellRendererParams,
} from 'ag-grid-community';

export default class TengwarRenderer implements ICellRendererComp {
    private _cell: HTMLElement;

    public init(params: ICellRendererParams) {
        const cell = document.createElement('div');
        cell.classList.add('tengwar');
        this._cell = cell;

        this.refresh(params);
    }

    public getGui() {
        return this._cell;
    }

    public refresh(params: ICellRendererParams) {
        this._cell.textContent = params.valueFormatted || params.value;
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}
