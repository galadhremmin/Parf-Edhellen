import type { IWordListEntry } from '@root/connectors/backend/IWordListApi';

export enum SortOrder {
    Manual = 'manual',
    Word = 'word',
    Translation = 'translation',
    Language = 'language',
    Type = 'type',
    DateAdded = 'date-added',
}

export interface IEntryRowProps {
    entry: IWordListEntry;
    canEdit: boolean;
    selected: boolean;
    /** Drag handles are only meaningful while the list is in manual order. */
    draggable: boolean;
    onSelectedChange: (lexicalEntryId: number, selected: boolean) => void;
    onRemove: (lexicalEntryId: number) => void;
    onDragStart: (lexicalEntryId: number) => void;
    onDragOver: (lexicalEntryId: number) => void;
    onDrop: () => void;
}
