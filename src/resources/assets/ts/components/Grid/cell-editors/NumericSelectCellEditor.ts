import { SelectCellEditor } from 'ag-grid-community';

export default class NumericSelectCellEditor extends SelectCellEditor {
    public getValue() {
        return parseInt(super.getValue(), 10);
    }
}
