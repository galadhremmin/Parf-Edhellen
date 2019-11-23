import React, {
    useCallback,
    useEffect,
    useRef,
} from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Pagination from '@root/components/Pagination';
import TextIcon from '@root/components/TextIcon';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import DiscussActions from '../actions/DiscussActions';
import Form from '../components/Form';
import {
    IFormChangeData,
    IFormOutput,
} from '../components/Form._types';
import Post from '../components/Post';
import { IProps as IPostProps } from '../components/Post._types';
import RespondButton from '../components/RespondButton';
import { IProps } from '../index._types';
import { RootReducer } from '../reducers';

import ConditionalToolbar from '../components/toolbar/ConditionalToolbar';

function Discuss(props: IProps) {
    const formRef = useRef(null);
    const paginationRef = useRef(null);

    const {
        currentPage,
        newPostContent,
        newPostEnabled,
        noOfPages,
        pages,
        posts,
        thread,
        threadMetadata,

        onExistingPostChange,
        onExistingThreadMetadataChange,
        onNewPostChange,
        onNewPostCreate,
        onNewPostSubmit,
        onNewPostDiscard,
        onPageChange,
    } = props;

    const {
        entityId,
        entityType,
        forumGroupId,
        id: threadId,
    } = thread;

    useEffect(() => {
        // If the customer wants to respond to the thread, ensure that the component scrolls
        // into view.
        if (newPostEnabled) {
            makeVisibleInViewport(formRef.current);
        }
    }, [ newPostEnabled, formRef ]);

    const _onCreateNewPost = useCallback(() => {
        fireEvent(null, onNewPostCreate);
    }, [ onNewPostCreate ]);

    const _onDiscardNewPost = useCallback(() => {
        fireEvent(null, onNewPostDiscard);
    }, [ onNewPostDiscard ]);

    const _onNewPostChange = useCallback((ev: IComponentEvent<IFormChangeData>) => {
        fireEvent(null, onNewPostChange, ev.value);
    }, [ onNewPostChange ]);

    const _onNewPostSubmit = useCallback((ev: IComponentEvent<IFormOutput>) => {
        fireEvent(null, onNewPostSubmit, {
            ...(threadId ? {
                forumThreadId: threadId,
            } : {
                entityId,
                entityType,
                forumGroupId,
            }),

            content: ev.value.content,
        });
    }, [ onNewPostSubmit, entityId, entityType, forumGroupId, threadId ]);

    const _onPaginate = useCallback((ev: IComponentEvent<number>) => {
        if (ev.value !== currentPage) {
            // Cancel editing mode
            _onDiscardNewPost();

            // The component is `null` because `this` reference is finicky for functional components.
            fireEvent(null, onPageChange, {
                pageNumber: ev.value,
                thread,
            });
        }
    }, [ currentPage, thread, _onDiscardNewPost, onPageChange ]);

    const _onGotoNavigation = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        makeVisibleInViewport(paginationRef.current);
    }, [ paginationRef ]);

    const _renderToolbar = useCallback((postProps: IPostProps) => {
        return <ConditionalToolbar
            onPostChange={onExistingPostChange}
            onThreadMetadataChange={onExistingThreadMetadataChange}
            post={postProps.post}
            thread={thread}
            threadMetadata={threadMetadata} />;
    }, [ onExistingPostChange, onExistingThreadMetadataChange, thread, threadMetadata ]);

    return <>
        {posts.map(
            (post) => <Post key={post.id}
                            post={post}
                            renderToolbar={_renderToolbar}
                      />,
        )}
        <aside ref={formRef} className="discuss-body__toolbar--primary">
            {newPostEnabled
                ? <Form name="discussForm"
                        content={newPostContent}
                        subjectEnabled={false}

                        onCancel={_onDiscardNewPost}
                        onChange={_onNewPostChange}
                        onSubmit={_onNewPostSubmit}
                  />
                : <RespondButton onClick={_onCreateNewPost} isNewPost={posts.length === 0} />}
        </aside>
        <div ref={paginationRef}>
            <Pagination currentPage={currentPage}
                noOfPages={noOfPages}
                onClick={_onPaginate}
                pages={pages}
            />
        </div>
        {posts.length > 0 && <a href="#" className="discuss-body__bottom" onClick={_onGotoNavigation}>
            <TextIcon icon="chevron-down" />
        </a>}
    </>;
}

const mapStateToProps = (state: RootReducer) => ({
    ...state.pagination,
    newPostContent: state.newPost.content,
    newPostEnabled: state.newPost.enabled,
    newPostLoading: state.newPost.loading,
    posts: state.posts,
    thread: state.thread,
    threadMetadata: state.threadMetadata,
} as Partial<IProps>);

const actions = new DiscussActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
    onExistingPostChange: (ev) => dispatch(actions.post({
        forumPostId: ev.value,
        includeDeleted: true,
    })),
    onExistingThreadMetadataChange: (ev) => dispatch(actions.threadMetadata(ev.value)),
    onNewPostChange: (ev) => dispatch(actions.changeNewPost({
        propertyName: ev.value.name,
        value: ev.value.value,
    })),
    onNewPostCreate: () => dispatch(actions.createNewPost()),
    onNewPostDiscard: () => dispatch(actions.discardNewPost()),
    onNewPostSubmit: (ev) => dispatch(actions.createPost(ev.value)),
    onPageChange: (ev) => dispatch(actions.thread({
        entityId: ev.value.thread.entityId,
        entityType: ev.value.thread.entityType,
        id: ev.value.thread.id,
        offset: ev.value.pageNumber,
    })),
} as Partial<IProps>);

export default connect(mapStateToProps, mapDispatchToProps)(Discuss);
