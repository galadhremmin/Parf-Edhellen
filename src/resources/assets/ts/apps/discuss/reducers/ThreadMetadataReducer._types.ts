import { IReduxAction } from '@root/_types';
import { IThreadMetadataResponse } from '@root/connectors/backend/IDiscussApi';
import { IThreadEntityAction } from './ThreadReducer._types';

export interface IThreadMetadataReducerAction extends IReduxAction, IThreadEntityAction {
    metadata: IThreadMetadataResponse;
    forumThreadId: number;
}

export interface IThreadMetadataState extends Partial<IThreadMetadataResponse> {
    loading: boolean;
}
