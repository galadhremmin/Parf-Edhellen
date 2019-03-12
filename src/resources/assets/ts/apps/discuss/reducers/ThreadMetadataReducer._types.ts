import { IReduxAction } from '@root/_types';
import { IThreadMetadataResponse } from '@root/connectors/backend/DiscussApiConnector._types';

export interface IThreadMetadataReducerAction extends IReduxAction {
    metadata: IThreadMetadataResponse;
}

export interface IThreadMetadataState extends IThreadMetadataResponse {
    loading: boolean;
}
