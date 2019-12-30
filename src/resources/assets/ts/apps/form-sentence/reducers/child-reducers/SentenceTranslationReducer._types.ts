import { IReduxAction } from '@root/_types';
import { ISentenceTranslationEntity } from '@root/connectors/backend/IBookApi';
import { Actions } from '../../actions';

export type ISentenceTranslationReducerState = Pick<ISentenceTranslationEntity, 'paragraphNumber' |
    'sentenceNumber' | 'translation'>;

export interface ISentenceTranslationAction extends IReduxAction<Actions> {
    sentenceTranslation: ISentenceTranslationReducerState;
}
