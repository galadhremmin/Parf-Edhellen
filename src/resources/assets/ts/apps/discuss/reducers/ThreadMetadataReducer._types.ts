import type { IReduxAction } from '@root/_types';
import type { IThreadMetadataResponse } from '@root/connectors/backend/IDiscussApi';
import type { IThreadEntityAction } from './ThreadReducer._types';

export interface IThreadMetadataReducerAction extends IReduxAction, IThreadEntityAction {
    metadata: IThreadMetadataResponse;
    forumThreadId: number;
}

export interface IThreadMetadataState extends Partial<IThreadMetadataResponse> {
    loading: boolean;
}
