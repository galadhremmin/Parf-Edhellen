import { ThunkDispatch } from 'redux-thunk';

import { SearchResultGroups } from '@root/config';
import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { IGlossesState } from '../reducers/GlossesReducer._types';

export interface IEntitiesComponentProps {
    dispatch?: ThunkDispatch<any, any, any>;
    glosses: IGlossesState;
    groupId?: keyof typeof SearchResultGroups;
    isEmpty: boolean;
    languages: ILanguageEntity[];
    loading: boolean;
    single: boolean;
    unusualLanguages: ILanguageEntity[];
    word: string;
}
