import { ThunkDispatch } from 'redux-thunk';

import {
    IBookGlossEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';
import { ISectionsState } from '../reducers/SectionsReducer._types';

export interface IEntitiesComponentProps<T = IBookGlossEntity> {
    dispatch?: ThunkDispatch<any, any, any>;
    groupId?: number;
    groupName?: string;
    isEmpty: boolean;
    languages?: ILanguageEntity[];
    loading: boolean;
    sections?: ISectionsState<T>;
    single: boolean;
    word: string;
    unusualLanguages?: ILanguageEntity[];
}
