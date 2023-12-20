import {
    Fragment,
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import AuthenticationDialog from '@root/components/AuthenticationDialog';
import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Pagination from '@root/components/Pagination';
import { PageModes } from '@root/components/Pagination/Pagination._types';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';
import { getStateOrDefault } from '@root/utilities/redux/collectivize';

import DiscussActions from '../actions/DiscussActions';
import Form from '../components/Form';
import {
    IFormChangeData,
    IFormOutput,
} from '../components/Form._types';
import PaginationDetails from '../components/PaginationDetails';
import Post from '../components/Post';
import { IProps as IPostProps } from '../components/Post._types';
import RespondButton from '../components/RespondButton';
import ConditionalToolbar from '../components/toolbar/ConditionalToolbar';
import { RootReducer, keyGenerator } from '../reducers';
import { IProps } from './Discuss._types';

function Discuss(props: IProps) {
    const formRef = useRef(null);
    const paginationRef = useRef(null);

    const [ promoteAuth, setPromoteAuth ] = useState(false);

    const {
        currentPage,
        highlightThreadPost,
        newPostContent,
        newPostEnabled,
        noOfPages,
        noOfPosts,
        posts,
        readonly,
        roleManager,
        thread,
        threadMetadata,

        onExistingPostChange,
        onExistingThreadChange,
        onExistingThreadMetadataChange,
        onNewPostChange,
        onNewPostCreate,
        onNewPostSubmit,
        onNewPostDiscard,
        onPageChange,
        onReferenceLinkClick,
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

    const _onAuthenticationRequired = useCallback(() => {
        setPromoteAuth(true);
    }, []);

    const _onAuthenticationCancelled = useCallback(() => {
        setPromoteAuth(false);
    }, []);

    const _onCreateNewPost = useCallback(() => {
        fireEvent(null, onNewPostCreate, {
            entityId,
            entityType,
        });
    }, [ onNewPostCreate, entityId, entityType ]);

    const _onDiscardNewPost = useCallback(() => {
        fireEvent(null, onNewPostDiscard, {
            entityId,
            entityType,
        });
    }, [ onNewPostDiscard, entityId, entityType ]);

    const _onNewPostChange = useCallback((ev: IComponentEvent<IFormChangeData>) => {
        fireEvent(null, onNewPostChange, {
            change: ev.value,
            entityId,
            entityType,
        });
    }, [ onNewPostChange, entityId, entityType ]);

    const _onNewPostSubmit = useCallback((ev: IComponentEvent<IFormOutput>) => {
        fireEvent(null, onNewPostSubmit, {
            ...(threadId ? {
                entityId,
                entityType,
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

            window.scroll(0, 0);
        }
    }, [ currentPage, thread, _onDiscardNewPost, onPageChange ]);

    const _renderToolbar = useCallback((postProps: IPostProps) => {
        return <ConditionalToolbar
            onAuthenticationRequired={_onAuthenticationRequired}
            onPostChange={onExistingPostChange}
            onThreadChange={onExistingThreadChange}
            onThreadMetadataChange={onExistingThreadMetadataChange}
            post={postProps.post}
            roleManager={roleManager}
            thread={thread}
            threadMetadata={threadMetadata} />;
    }, [
        onExistingPostChange,
        onExistingThreadChange,
        onExistingThreadMetadataChange,
        thread,
        threadMetadata,
    ]);

    return <>
        {posts.map(
            (post) => <Fragment key={`${currentPage}.${post.id}`}>
                <Post
                    highlightThreadPost={highlightThreadPost}
                    onReferenceLinkClick={onReferenceLinkClick}
                    post={post}
                    renderToolbar={_renderToolbar}
                />
                {post._isThreadPost && <PaginationDetails
                    currentPage={currentPage}
                    numberOfPages={noOfPages}
                    numberOfPosts={posts.length}
                    numberOfTotalPosts={noOfPosts}
                />}
            </Fragment>,
        )}
        {posts.length > 0 && <div ref={paginationRef}>
            <Pagination currentPage={currentPage}
                noOfPages={noOfPages}
                onClick={_onPaginate}
                pages={PageModes.AutoGenerate}
            />
        </div>}
        {(! readonly || roleManager.isAdministrator) && <aside ref={formRef} className="discuss-body__toolbar--primary mb-3 mt-3 text-center">
            {newPostEnabled
                ? <Form name="discussForm"
                        content={newPostContent}
                        subjectEnabled={false}

                        onCancel={_onDiscardNewPost}
                        onChange={_onNewPostChange}
                        onSubmit={_onNewPostSubmit}
                  />
                : <RespondButton onClick={_onCreateNewPost} isNewPost={posts.length === 0} />}
        </aside>}
        <AuthenticationDialog onDismiss={_onAuthenticationCancelled} open={promoteAuth} />
    </>;
}

const mapStateToProps = (state: RootReducer, ownProps: IProps) => {
    const {
        entityId,
        entityType,
    } = ownProps;
    const {
        paginations,
        newPosts,
        posts: allPosts,
        threads,
        threadMetadatas,
    } = state;

    const key = keyGenerator(entityType, entityId);

    const pagination = getStateOrDefault(paginations, key);
    const thread = getStateOrDefault(threads, key);
    const threadMetadata = getStateOrDefault(threadMetadatas, key);
    const newPost = getStateOrDefault(newPosts, key);
    const posts = allPosts.filter(p => p.forumThreadId === thread.id);

    return {
        ...pagination,
        newPostContent: newPost?.content || '',
        newPostEnabled: newPost?.enabled || false,
        posts,
        thread,
        threadMetadata,
        roleManager: resolve(DI.RoleManager),
    } as Partial<IProps>;
};

const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => {
    const actions = new DiscussActions();
    return {
        onExistingPostChange: (ev) => dispatch(actions.post({
            forumPostId: ev.value,
            includeDeleted: true,
        })),
        onExistingThreadChange: (ev) => dispatch(actions.thread({
            id: ev.value,
        })),
        onExistingThreadMetadataChange: (ev) => dispatch(actions.threadMetadata(ev.value)),
        onNewPostChange: (ev) => dispatch(actions.changeNewPost({
            entityId: ev.value.entityId,
            entityType: ev.value.entityType,
            propertyName: ev.value.change.name,
            value: ev.value.change.value,
        })),
        onNewPostCreate: (ev) => dispatch(actions.createNewPost(ev.value)),
        onNewPostDiscard: (ev) => dispatch(actions.discardNewPost(ev.value)),
        onNewPostSubmit: (ev) => dispatch(actions.createPost(ev.value)),
        onPageChange: (ev) => dispatch(actions.thread({
            entityId: ev.value.thread.entityId,
            entityType: ev.value.thread.entityType,
            id: ev.value.thread.id,
            offset: ev.value.pageNumber,
        })),
        onReferenceLinkClick: (ev) => {
            const globalEvent = resolve(DI.GlobalEvents);
            globalEvent.fire(globalEvent.loadReference, ev.value);
        },
    } as Partial<IProps>;
};

export default connect<Partial<IProps>, Partial<IProps>, IProps>(mapStateToProps, mapDispatchToProps)(Discuss);
