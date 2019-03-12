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
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import DiscussActions from '../actions/DiscussActions';
import Form from '../components/Form';
import {
    IFormChangeData,
    IFormOutput,
} from '../components/Form._types';
import Post from '../components/Post';
import RespondButton from '../components/RespondButton';
import { IProps } from '../index._types';
import { RootReducer } from '../reducers';

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

        onNewPostChange,
        onNewPostCreate,
        onNewPostSubmit,
        onNewPostDiscard,
        onPageChange,
    } = props;

    const {
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
            content: ev.value.content,
            forumThreadId: threadId,
        });
    }, [ onNewPostSubmit, threadId ]);

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

    return <>
        {posts.map((post) => <Post post={post} key={post.id} />)}
        <aside ref={formRef} className="discuss-body__toolbar--primary">
            {newPostEnabled
                ? <Form name="discussForm"
                        content={newPostContent}
                        subjectEnabled={false}

                        onCancel={_onDiscardNewPost}
                        onChange={_onNewPostChange}
                        onSubmit={_onNewPostSubmit}
                  />
                : <RespondButton onClick={_onCreateNewPost} />}
        </aside>
        <div ref={paginationRef}>
            <Pagination currentPage={currentPage}
                noOfPages={noOfPages}
                onClick={_onPaginate}
                pages={pages}
            />
        </div>
        <a href="#" className="discuss-body__bottom" onClick={_onGotoNavigation}>
            <span className="glyphicon glyphicon-chevron-down" />
        </a>
    </>;
}

const mapStateToProps = (state: RootReducer) => ({
    ...state.pagination,
    newPostContent: state.newPost.content,
    newPostEnabled: state.newPost.enabled,
    newPostLoading: state.newPost.loading,
    posts: state.posts,
    thread: state.thread,
} as Partial<IProps>);

const actions = new DiscussActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
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
