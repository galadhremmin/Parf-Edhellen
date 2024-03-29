import {
    ColDef,
} from 'ag-grid-community';

import { ComponentEventHandler } from '@root/components/Component._types';
import { IFragmentGridMetadata } from '@root/components/Grid/cell-editors/InflectionCellEditor._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import IGlossResourceApi from '@root/connectors/backend/IGlossResourceApi';
import { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { ISentenceFragmentReducerState } from '../../reducers/child-reducers/SentenceFragmentReducer._types';

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

    glossApi?: IGlossResourceApi;
    inflectionApi?: IInflectionResourceApi;
    speechApi?: ISpeechResourceApi;
}

export interface IState extends IFragmentGridMetadata {
    columnDefinition?: FragmentGridColumnDefinition;
}
