import { IReduxAction } from '@root/_types';
import { IThreadMetadataResponse } from '@root/connectors/backend/IDiscussApi';

export interface IThreadMetadataReducerAction extends IReduxAction {
    metadata: IThreadMetadataResponse;
}

export interface IThreadMetadataState extends Partial<IThreadMetadataResponse> {
    loading: boolean;
}
