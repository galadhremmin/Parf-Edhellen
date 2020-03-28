import {
    ColDef,
    ICellEditorParams,
    ICellRendererParams,
} from '@ag-grid-community/all-modules';

import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import {
    IGlossEntity,
    ISuggestionEntity,
} from '@root/connectors/backend/IGlossResourceApi';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';
import { ISentenceFragmentReducerState } from '../../reducers/child-reducers/SentenceFragmentReducer._types';

export type FragmentGridColumnDefinition = (Partial<ColDef> & {
    cellRendererParams?: IFragmentGridMetadata;
    field: keyof ISentenceFragmentReducerState,
})[];

export interface IFragmentGridMetadata extends Partial<ICellEditorParams> {
    groupedInflections?: Map<string, IInflection[]>;
    inflections?: Map<number, IInflection>;
    resolveGloss?: (glossId: number) => Promise<IGlossEntity>;
    speeches?: Map<number, ISpeechEntity>;
    suggestGloss?: (text: string) => Promise<ISuggestionEntity[]>;
}

export type IAugmentedCellRendererParams = IFragmentGridMetadata & Partial<ICellRendererParams>;

export interface IFragmentChangeEventArgs<T extends keyof ISentenceFragmentEntity = keyof ISentenceFragmentEntity> {
    field: T;
    fragment: ISentenceFragmentEntity;
    value: ISentenceFragmentEntity[T];
}

export interface IProps {
    fragments: ISentenceFragmentReducerState[];
    languageId: number;
    onChange: ComponentEventHandler<IFragmentChangeEventArgs>;
}

export interface IState extends IFragmentGridMetadata {
    columnDefinition?: FragmentGridColumnDefinition;
}
