import type { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';
import type { IReduxAction } from '@root/_types';

export interface IInflectionAction extends IReduxAction {
    inflectionGroupUuid: string;
    order: number;
    preloadedInflections: {
        [inflectionGroupUuid: string]: ILexicalEntryInflection[]
    };
    inflectionGroup: IInflectionGroupState;
}

export interface IInflectionGroupState extends Partial<Pick<ILexicalEntryInflection, 'inflectionGroupUuid' | 
    'isNeologism' | 'isRejected' | 'languageId' | 'sentenceFragmentId' | 'source' | 'speechId' | 'word'>> {
    inflections: ILexicalEntryInflection[];
}

export type GroupedInflectionsState = IInflectionGroupState[];
