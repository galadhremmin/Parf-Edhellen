import { ICellEditorParams } from 'ag-grid-community';

import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { IFragmentGridMetadata } from './InflectionCellEditor._types';
import MultipleSelectCellEditor from './MultipleSelectCellEditor';

export default class InflectionCellEditor extends MultipleSelectCellEditor<IInflection, IGlossInflection> {
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

    protected isOptionAvailable(option: IInflection) {
        return ! option.isRestricted;
    }

    protected getOptionId(option: IInflection) {
        return option.id;
    }

    protected getOptionText(option: IInflection) {
        return option.name;
    }

    protected getValueId(value: IGlossInflection) {
        return value.inflectionId;
    }

    protected getValueText(value: IGlossInflection) {
        const id = this.getValueId(value);
        return this._inflections.has(id) ? this._inflections.get(id).name : '⚠ invalid';
    }

    protected convertOptionToValue(option: IInflection): IGlossInflection {
        return {
            inflectionId: option.id,
        };
    }
}
