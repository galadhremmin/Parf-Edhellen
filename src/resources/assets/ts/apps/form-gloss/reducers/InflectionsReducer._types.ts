import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IReduxAction } from '@root/_types';

export interface IInflectionAction extends IReduxAction {
    inflectionGroupUuid: string;
    order: number;
    preloadedInflections: {
        [inflectionGroupUuid: string]: IGlossInflection[]
    };
    inflectionGroup: IInflectionGroupState;
}

export interface IInflectionGroupState extends Partial<Pick<IGlossInflection, 'inflectionGroupUuid' | 
    'isNeologism' | 'isRejected' | 'languageId' | 'sentenceFragmentId' | 'source' | 'speechId' | 'word'>> {
    inflections: IGlossInflection[];
}

export type GroupedInflectionsState = IInflectionGroupState[];
