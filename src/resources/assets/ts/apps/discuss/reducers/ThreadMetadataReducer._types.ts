import { IReduxAction } from '@root/_types';
import { IThreadMetadataResponse } from '@root/connectors/backend/IDiscussApi';

export interface IThreadMetadataReducerAction extends IReduxAction {
    metadata: IThreadMetadataResponse;
    forumThreadId: number;
}

export interface IThreadMetadataState extends Partial<IThreadMetadataResponse> {
    loading: boolean;
    forumThreadId: number;
}
