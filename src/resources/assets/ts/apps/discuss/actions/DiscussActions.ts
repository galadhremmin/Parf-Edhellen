import { ThunkDispatch } from 'redux-thunk';

import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import BrowserHistory from '@root/utilities/BrowserHistory';
import SharedReference from '@root/utilities/SharedReference';

import {
    IChangePostAction,
    ICreatePostAction,
    IThreadAction,
} from '../reducers/ThreadReducer._types';
import Actions from './Actions';

export default class DiscussActions {
    constructor(private _api = new SharedReference(DiscussApiConnector)) {
    }

    public thread(args: IThreadAction) {
        return async (dispatch: ThunkDispatch<any, any, any>) => {
            dispatch({
                type: Actions.RequestThread,
            });

            const threadData = await this._api.value.thread(args);

            // Update the browser's current page (in the event that the client refreshes the window)
            BrowserHistory.default.push(`?offset=${threadData.currentPage}`);

            // Select the forum post that the client wants to load by setting its `_focused` to `true`.
            if (args.forumPostId !== undefined) {
                const post = threadData.posts.find((p) => p.id === args.forumPostId);
                if (post !== null) {
                    post._focused = true;
                }
            }

            dispatch({
                threadData,
                type: Actions.ReceiveThread,
            });
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

    public createPost(args: ICreatePostAction) {
        return async (dispatch: ThunkDispatch<any, any, any>) => {
            try {
                dispatch({
                    type: Actions.RequestCreatePost,
                });

                const postData = await this._api.value.create(args);

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
