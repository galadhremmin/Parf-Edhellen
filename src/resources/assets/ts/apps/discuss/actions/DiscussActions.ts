import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import IDiscussApi, {
    IPostResponse,
    IThreadResponse,
} from '@root/connectors/backend/IDiscussApi';
import BrowserHistory from '@root/utilities/BrowserHistory';
import SharedReference from '@root/utilities/SharedReference';

import { RootReducer } from '../reducers';
import {
    IChangePostAction,
    ICreatePostAction,
    IPostAction,
    IThreadAction,
    IThreadMetadataAction,
} from '../reducers/ThreadReducer._types';
import Actions from './Actions';

export default class DiscussActions {
    constructor(private _api: SharedReference<IDiscussApi> = new SharedReference(DiscussApiConnector)) {
    }

    public thread(args: IThreadAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            dispatch({
                type: Actions.RequestThread,
            });

            const threadData = await this._api.value.thread(args);
            dispatch(this.setThread(threadData));
        };
    }

    public setThread(threadData: IThreadResponse): ReduxThunk {
        // Update the browser's current page (in the event that the client refreshes the window)
        const browserHistory = SharedReference.getInstance(BrowserHistory);
        browserHistory.push(`?offset=${threadData.currentPage}`);

        return async (dispatch: ReduxThunkDispatch) => {
            dispatch({
                threadData,
                type: Actions.ReceiveThread,
            });

            dispatch(this.threadMetadata({
                forumPostId: threadData.posts.map((p) => p.id),
                forumThreadId: threadData.thread.id,
            }));
        };
    }

    public threadMetadata(args: IThreadMetadataAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch, getState: () => RootReducer) => {
            // Bail if this request is already being processed.
            if (getState().threadMetadata.loading ||
                args.forumPostId.length < 1 ||
                args.forumThreadId === null) {
                return;
            }

            dispatch({
                type: Actions.RequestThreadMetadata,
            });

            const metadata = await this._api.value.threadMetadata(args);
            dispatch({
                metadata,
                type: Actions.ReceiveThreadMetadata,
            });
        };
    }

    public post(args: IPostAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const postData = await this._api.value.post(args);
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

    public createNewPost() {
        return {
            type: Actions.CreateNewPost,
        };
    }

    public changeNewPost(args: IChangePostAction) {
        return {
            type: Actions.ChangeNewPost,
            ...args,
        };
    }

    public discardNewPost() {
        return {
            type: Actions.DiscardNewPost,
        };
    }

    public createPost(args: ICreatePostAction): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            try {
                dispatch({
                    type: Actions.RequestCreatePost,
                });

                const postData = await this._api.value.createPost(args);

                dispatch({
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
