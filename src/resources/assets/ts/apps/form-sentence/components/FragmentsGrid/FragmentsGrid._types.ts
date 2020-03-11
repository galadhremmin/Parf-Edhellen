import {
    ColDef,
    ICellEditorParams,
    ICellRendererParams,
} from '@ag-grid-community/all-modules';

import {
    ISentenceFragmentEntity,
} from '@root/connectors/backend/IBookApi';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';
import {
    IInflection,
    IInflectionMap,
} from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

export type FragmentGridColumnDefinition = (Partial<ColDef> & {
    cellRendererParams?: IFragmentGridMetadata;
    field: keyof ISentenceFragmentEntity,
})[];

export interface IFragmentGridMetadata extends Partial<ICellEditorParams> {
    groupedInflections?: IInflectionMap;
    inflections?: Map<number, IInflection>;
    resolveGloss?: (glossId: number) => Promise<IGlossEntity>;
    speeches?: Map<number, ISpeechEntity>;
}

export type IAugmentedCellRendererParams = IFragmentGridMetadata & Partial<ICellRendererParams>;

export interface IProps {
    fragments: ISentenceFragmentEntity[];
}

export interface IState extends IFragmentGridMetadata {
    columnDefinition?: FragmentGridColumnDefinition;
}
