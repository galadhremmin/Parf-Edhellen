import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import IDiscussApi, {
    IPostResponse,
    IThreadResponse,
} from '@root/connectors/backend/IDiscussApi';
import { DI, resolve } from '@root/di';
import BrowserHistory from '@root/utilities/BrowserHistory';

import { keyGenerator, RootReducer } from '../reducers';
import {
    IChangePostAction,
    ICreatePostAction,
    IPostAction,
    IThreadAction,
    IThreadEntityAction,
    IThreadMetadataAction,
} from '../reducers/ThreadReducer._types';
import Actions from './Actions';

export default class DiscussActions {
    constructor(private _api: IDiscussApi = resolve(DI.DiscussApi)) {
    }

    public thread(args: IThreadAction, jump = true): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const {
                entityId,
                entityType,
            } = args;
            if (entityId && entityType) {
                dispatch({
                    ...args,
                    type: Actions.RequestThread,
                });
            }

            const threadData = await this._api.thread(args);
            dispatch(this.setThread(threadData, false, jump));
        };
    }

    public setThread(threadData: IThreadResponse, updateHistory = false, jump = true): ReduxThunk {
        if (updateHistory) {
            // Update the browser's current page (in the event that the client refreshes the window)
            const browserHistory = resolve<BrowserHistory>(DI.BrowserHistory);
            const {
                currentPage,
            } = threadData;

            if (currentPage !== undefined) {
                browserHistory.push(`?offset=${currentPage}`);
            }
        }

        return (dispatch: ReduxThunkDispatch) => {
            const {
                entityId,
                entityType,
            } = threadData.thread;

            if (! jump) {
                threadData.jumpPostId = null;
            }

            dispatch({
                entityId,
                entityType,
                threadData,
                type: Actions.ReceiveThread,
            });

            dispatch(this.threadMetadata({
                entityId,
                entityType,
                forumPostId: threadData.posts.map((p) => p.id),
                forumThreadId: threadData.thread.id,
            }));
        };
    }

    public threadMetadata(args: IThreadMetadataAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            // Bail if this request is already being processed.
            const {
                entityId,
                entityType,
            } = args;
            const key = keyGenerator(entityType, entityId);
            const metadata = getState().threadMetadatas[key];
            if (metadata?.loading ||
                args.forumPostId.length < 1 ||
                args.forumThreadId === null) {
                return;
            }

            dispatch({
                entityId,
                entityType,
                type: Actions.RequestThreadMetadata,
            });

            const newMetadata = await this._api.threadMetadata(args);
            dispatch({
                entityId,
                entityType,
                metadata: newMetadata,
                type: Actions.ReceiveThreadMetadata,
            });
        };
    }

    public post(args: IPostAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const postData = await this._api.post(args);
            dispatch(this.setPost(postData));
        };
    }

    public setPost(postData: IPostResponse) {
        return {
            postData,
            type: postData.post.isDeleted
                ? Actions.DeletePost
                : Actions.UpdatePost,
        };
    }

    public createNewPost(args: IThreadEntityAction) {
        return {
            ...args,
            type: Actions.CreateNewPost,
        };
    }

    public changeNewPost(args: IChangePostAction) {
        return {
            ...args,
            type: Actions.ChangeNewPost,
        };
    }

    public discardNewPost(args: IThreadEntityAction) {
        return {
            ...args,
            type: Actions.DiscardNewPost,
        };
    }

    public createPost(args: ICreatePostAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            try {
                const {
                    entityId,
                    entityType,
                } = args;
                dispatch({
                    entityId,
                    entityType,
                    type: Actions.RequestCreatePost,
                });

                const postData = await this._api.createPost(args);

                dispatch({
                    entityId,
                    entityType,
                    type: Actions.ReceiveCreatePost,
                });

                dispatch(this.thread({
                    entityId: postData.thread.entityId,
                    entityType: postData.thread.entityType,
                    forumPostId: postData.post.id,
                    id: postData.thread.id,
                }));
            } catch (e) {
                // TODO: What do we do here?
            }
        };
    }
}
