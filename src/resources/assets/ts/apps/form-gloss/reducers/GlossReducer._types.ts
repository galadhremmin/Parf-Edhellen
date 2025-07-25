import { IReduxAction } from '@root/_types';
import { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import { ILexicalEntryEntity } from '@root/connectors/backend/IGlossResourceApi';
import { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';

export type IGlossState = IContribution<Pick<ILexicalEntryEntity, 'account' | 'comments' |
    'etymology' | 'externalId' | 'lexicalEntryDetails' | 'lexicalEntryGroupId' | 'id' |
    'isRejected' | 'isUncertain' | 'keywords' | 'label' | 'languageId' | 'latestLexicalEntryVersionId' |
    'sense' | 'source' | 'speechId' | 'tengwar' | 'translations' | 'word'>>;

export interface IGlossAction extends IReduxAction {
    gloss: IContribution<ILexicalEntryEntity>;
    field: string;
    value: string;
}
