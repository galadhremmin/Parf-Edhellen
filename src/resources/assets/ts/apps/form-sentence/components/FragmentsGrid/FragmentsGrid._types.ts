import type {
    ColDef,
} from '@ag-grid-community/core';

import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IFragmentGridMetadata } from '@root/components/Grid/cell-editors/InflectionCellEditor._types';
import type { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import type ILexicalEntryResourceApi from '@root/connectors/backend/IGlossResourceApi';
import type { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import type ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import type { ISentenceFragmentReducerState } from '../../reducers/child-reducers/SentenceFragmentReducer._types';

export type FragmentGridColumnDefinition = (Partial<ColDef> & {
    cellRendererParams?: IFragmentGridMetadata;
    field: keyof ISentenceFragmentReducerState,
})[];

export interface IFragmentChangeEventArgs<T extends keyof ISentenceFragmentEntity = keyof ISentenceFragmentEntity> {
    field: T;
    fragment: ISentenceFragmentEntity;
    value: ISentenceFragmentEntity[T];
}

export interface IProps {
    fragments: ISentenceFragmentReducerState[];
    languageId: number;
    onChange: ComponentEventHandler<IFragmentChangeEventArgs>;

    glossApi?: ILexicalEntryResourceApi;
    inflectionApi?: IInflectionResourceApi;
    speechApi?: ISpeechResourceApi;
}

export interface IState extends IFragmentGridMetadata {
    columnDefinition?: FragmentGridColumnDefinition;
}
