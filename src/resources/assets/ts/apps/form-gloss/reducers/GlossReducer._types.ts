import { IReduxAction } from '@root/_types';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';

export type IGlossState = Pick<IGlossEntity, 'account' | 'comments' |
    'etymology' | 'externalId' | 'glossDetails' | 'glossGroupId' | 'id' |
    'isRejected' | 'isUncertain' | 'keywords' | 'languageId' | 'phonetic' |
    'sense' | 'source' | 'speechId' | 'tengwar' | 'translations' | 'word'>;

export interface IGlossAction extends IReduxAction {
    gloss: IGlossEntity;
    field: string;
    value: string;
}
