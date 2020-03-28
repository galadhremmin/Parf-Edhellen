import { IReduxAction } from '@root/_types';
import {
    ISentenceEntity,
} from '@root/connectors/backend/IBookApi';
import { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import { Actions } from '../actions';

export type ISentenceReducerState = IContribution<Pick<ISentenceEntity, 'account' |
    'description' | 'id' | 'isApproved' | 'isNeologism' |  'language' |
    'languageId' | 'longDescription' | 'name' | 'source'>>;

export interface ISentenceAction extends IReduxAction<Actions> {
    field?: string;
    sentence: IContribution<ISentenceReducerState>;
    value?: any;
}
