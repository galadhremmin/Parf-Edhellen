import { IReduxAction } from '@root/_types';
import { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';

export type IGlossState = IContribution<Pick<IGlossEntity, 'account' | 'comments' |
    'etymology' | 'externalId' | 'glossDetails' | 'glossGroupId' | 'id' |
    'isRejected' | 'isUncertain' | 'keywords' | 'label' | 'languageId' | 'latestGlossVersionId' |
    'sense' | 'source' | 'speechId' | 'tengwar' | 'translations' | 'word'>>;

export interface IGlossAction extends IReduxAction {
    gloss: IContribution<IGlossEntity>;
    field: string;
    value: string;
}
