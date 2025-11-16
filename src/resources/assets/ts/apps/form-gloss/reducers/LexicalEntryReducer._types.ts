import type { IReduxAction } from '@root/_types';
import type { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IGlossResourceApi';

export type ILexicalEntryState = IContribution<Pick<ILexicalEntryEntity, 'account' | 'comments' |
    'etymology' | 'externalId' | 'lexicalEntryDetails' | 'lexicalEntryGroupId' | 'id' |
    'isRejected' | 'isUncertain' | 'keywords' | 'label' | 'languageId' | 'latestLexicalEntryVersionId' |
    'sense' | 'source' | 'speechId' | 'tengwar' | 'glosses' | 'word'>>;

export interface ILexicalEntryAction extends IReduxAction {
    lexicalEntry: IContribution<ILexicalEntryEntity>;
    field: string;
    value: string;
}
