import type {
    ICellRendererComp,
    ICellRendererParams,
} from '@ag-grid-community/core';

import type { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';
import type { IAugmentedCellRendererParams } from '../cell-editors/InflectionCellEditor._types';

export default class InflectionRenderer implements ICellRendererComp {
    private _cell: HTMLDivElement;
    private _lastFormattedValue: string | null = null;
    private _lastSelectedInflections: ILexicalEntryInflection[] | null = null;
    private _lastInflections: Map<number, any> | null = null;

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
        const selectedInflections = params.value as ILexicalEntryInflection[];
        
        // Check if we can reuse the cached result
        if (this._lastFormattedValue !== null && 
            this._lastSelectedInflections === selectedInflections && 
            this._lastInflections === inflections) {
            return true;
        }

        // Cache the current values
        this._lastSelectedInflections = selectedInflections;
        this._lastInflections = inflections;

        // Perform the expensive operation
        const formatted = selectedInflections?.filter( //
                (i) => inflections.has(i.inflectionId), //
            ) //
             .map((i) => inflections.get(i.inflectionId).name) //
             .join(', ') || '';

        this._lastFormattedValue = formatted;
        this._cell.textContent = formatted;
        return true;
    }

    public destroy() {
        this._cell = null;
        this._lastFormattedValue = null;
        this._lastSelectedInflections = null;
        this._lastInflections = null;
    }
}
