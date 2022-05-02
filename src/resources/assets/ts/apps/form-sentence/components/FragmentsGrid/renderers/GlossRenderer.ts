import {
    ICellRendererComp,
    ICellRendererParams,
} from '@ag-grid-community/core';
import { IFragmentGridMetadata } from '../FragmentsGrid._types';

export default class GlossRenderer implements ICellRendererComp {
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
        const {
            resolveGloss,
            value,
        } = params as IFragmentGridMetadata;

        const cell = this._cell;

        resolveGloss(value).then((gloss) => {
            const translations = gloss.translations.map((t) => t.translation);
            cell.textContent = `${gloss.word.word} “${translations.join(', ')}” (${gloss.id})`;
        }).catch(() => {
            cell.textContent = '⚠ Invalid';
        });
        return true;
    }

    public destroy() {
        this._cell = null;
    }
}
