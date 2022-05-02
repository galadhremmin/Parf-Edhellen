import { ICellEditorParams } from '@ag-grid-community/core';

import { ISentenceFragmentInflection } from '@root/connectors/backend/IBookApi';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { IFragmentGridMetadata } from '../FragmentsGrid._types';
import MultipleSelectCellEditor from './MultipleSelectCellEditor';

export default class InflectionCellEditor extends MultipleSelectCellEditor<IInflection, ISentenceFragmentInflection> {
    private _editorParams: ICellEditorParams;

    public init(params: ICellEditorParams): void {
        this._editorParams = params;
        super.init(params);
    }

    protected get allOptions() {
        return (this._editorParams as IFragmentGridMetadata).groupedInflections;
    }

    private get _inflections() {
        return (this._editorParams as IFragmentGridMetadata).inflections;
    }

    protected getOptionId(option: IInflection) {
        return option.id;
    }

    protected getOptionText(option: IInflection) {
        return option.name;
    }

    protected getValueId(value: ISentenceFragmentInflection) {
        return value.inflectionId;
    }

    protected getValueText(value: ISentenceFragmentInflection) {
        const id = this.getValueId(value);
        return this._inflections.has(id) ? this._inflections.get(id).name : '⚠ invalid';
    }

    protected convertOptionToValue(option: IInflection): ISentenceFragmentInflection {
        return {
            inflectionId: option.id,
        };
    }
}
