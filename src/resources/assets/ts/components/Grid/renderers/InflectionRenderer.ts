import {
    ICellRendererComp,
    ICellRendererParams,
} from 'ag-grid-community';

import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IAugmentedCellRendererParams } from '../cell-editors/InflectionCellEditor._types';

export default class InflectionRenderer implements ICellRendererComp {
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
        const inflections = (params as IAugmentedCellRendererParams).inflections;
        const selectedInflections = params.value as IGlossInflection[];
        const formatted = selectedInflections //
            .filter((i) => inflections.has(i.inflectionId)) //
            .map((i) => inflections.get(i.inflectionId).name) //
            .join(', ');

        this._cell.textContent = formatted;
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}
