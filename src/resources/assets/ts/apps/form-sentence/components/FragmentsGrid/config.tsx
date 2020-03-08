import { ColDef } from '@ag-grid-community/all-modules';

import {
    ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';

import SpeechRenderer from './renderers/SpeechRenderer';
import TengwarRenderer from './renderers/TengwarRenderer';

type FragmentGridColumnDefinition = (Partial<ColDef> & {
    field: keyof ISentenceFragmentEntity,
})[];

export const FragmentGridColumns: FragmentGridColumnDefinition = [
    {
        editable: false,
        field: 'fragment',
    },
    {
        cellRenderer: TengwarRenderer,
        editable: false,
        field: 'tengwar',
    },
    {
        cellRenderer: SpeechRenderer,
        editable: true,
        field: 'speechId',
    },
    {
        editable: true,
        field: 'glossId',
    },
    {
        editable: true,
        field: 'comments',
    },
];

export const RelevantFragmentTypes = [
    SentenceFragmentType.Word,
];
