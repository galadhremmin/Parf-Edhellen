import { ICellEditorParams } from '@ag-grid-community/all-modules';

import { IFragmentGridMetadata } from '../FragmentsGrid._types';
import MultipleSelectCellEditor from './MultipleSelectCellEditor';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

export default class SpeechSelectCellEditor extends MultipleSelectCellEditor<ISpeechEntity, number> {
    private _allOptions: Set<ISpeechEntity>;
    private _editorParams: IFragmentGridMetadata;

    protected get maxNumberOfValues() {
        return 1;
    }

    public init(params: ICellEditorParams): void {
        const editorParams = params as IFragmentGridMetadata;
        this._editorParams = editorParams;
        this._allOptions = new Set(editorParams.speeches.values());
        super.init(params);
    }

    protected get allOptions() {
        return this._allOptions;
    }

    private get _speeches() {
        return (this._editorParams as IFragmentGridMetadata).speeches;
    }

    protected getOptionId(option: ISpeechEntity) {
        return option.id;
    }

    protected getOptionText(option: ISpeechEntity) {
        return option.name;
    }

    protected getValueId(value: number) {
        return value;
    }

    protected getValueText(value: number) {
        const id = this.getValueId(value);
        return this._speeches.has(id) ? this._speeches.get(id).name : '⚠ invalid';
    }

    protected convertOptionToValue(option: ISpeechEntity): number {
        return option.id;
    }
}
