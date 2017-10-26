import React from 'react';
import axios from 'axios';
import EDConfig from 'ed-config';
import classNames from 'classnames';
import { EDStatefulFormComponent } from 'ed-form';
import { Parser as HtmlToReactParser } from 'html-to-react';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';

class EDComments extends EDStatefulFormComponent {
    constructor(props) {
        super(props);

        let jump_post_id = 0;
        if (window.location.search) {
            let match = /&?forum_post_id=([0-9]+)/.exec(location.search.substr(1));
            jump_post_id = parseInt(match[1], 10);
        }

        this.state = {
            comments: '',
            posts: [],
            post_id: 0,
            highlighted_post_id: jump_post_id,
            loading: false,
            show_reply: false,
            jump_post_id
        };

        enableSmoothScrolling();
    }

    componentWillMount() {
        if (this.isInfiniteScroll()) {
            window.addEventListener('scroll', this.onScroll.bind(this));
        }
    }

    componentDidMount() {
        // Load comments if the client is specifically requesting to display them.
        if (this.state.jump_post_id || ! this.isInfiniteScroll()) {
            this.load();
        } else {
            this.onScroll();
        }
    }

    isInfiniteScroll() {
        return this.isDescendingOrder();
    }

    isDescendingOrder() {
        return this.props.order === 'desc';
    }

    login() {
        window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
    }

    load(fromId = this.state.jump_post_id || 0, parentPostId) {
        this.setState({
            loading: true
        });

        if (this.isInfiniteScroll() && this.state.jump_post_id > 0 && fromId === this.state.highlighted_post_id) {
            // retract once, as the from_id parameter will _not_ be included in the result set in
            // infinite scroll mode, as it assumes it is already loaded.
            fromId += 1;
        }

        const url = EDConfig.api(
            `/forum?morph=${this.props.morph}&entity_id=${this.props.entityId}&order=${this.props.order}` + 
            (fromId ? `&from_id=${fromId}` : '')
        );
        
        return axios.get(url).then(this.onLoaded.bind(this, fromId || 0));
    }

    onLoaded(fromId, response) {

        let posts = this.state.posts || [];
        const newPosts = response.data.posts || [];

        // First, jump to the post that the client has explicitly specified,
        // alternatively, when in ascending order, jump to the last comment 
        // (this the comment with the largest ID).
        const jumpPostId = this.state.jump_post_id || 
            (! this.isInfiniteScroll() && newPosts.length ? newPosts[newPosts.length - 1].id : 0);

        if (fromId === 0 || ! this.isInfiniteScroll()) {
            // reload -- start over from the beginning
            posts = newPosts;

            // record the current location, as we are reloading
            this.lastPositionY = window.scrollY || window.pageYOffset;

        } else if (posts.length < 1 || posts[0].id === this.state.major_id) {
            // prepend
            posts = [...newPosts, ...posts];
        } else {
            // append
            posts = [...posts, ...newPosts];
        }

        this.setState({
            major_id: response.data.major_id,
            pages: response.data.pages,
            jump_post_id: 0,
            loading: false,
            posts
        });

        if (jumpPostId) {
            window.setTimeout(() => {
                const id = `forum-post-${jumpPostId}`;
                const postContainer = document.getElementById(id);

                if (postContainer) {
                    postContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }, 500);
        }
    }

    onScroll(ev) {
        const scrollY = window.scrollY || window.pageYOffset;
        const lastY = this.lastPositionY;

        // Check whether the user is scrolling down, and cancel if it is not.
        if (scrollY < lastY) {
            return;
        }

        this.lastPositionY = scrollY;
        if (! this.container || this.loading) {
            return;
        }

        const contentRect = this.container.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        // Check if the bottom part of the component is within the viewport
        if (contentRect.bottom < 0 || contentRect.bottom > viewportHeight) {
            return;
        }

        this.loading = true;
        this.load(this.state.major_id).then(() => {
            // pause a while before restoring the capacity to load new comments, to give the 
            // browser some time to render, and the server some space to breathe.
            window.setTimeout(() => {
                this.loading = false;
            }, 1000);
        });
    }

    onSubmit(ev) {
        ev.preventDefault();

        const data = {
            comments: this.state.comments,
            morph: this.props.morph,
            entity_id: this.props.entityId
        };
        
        const promise = this.state.post_id === 0
            ? axios.post(EDConfig.api('/forum'), data)
            : axios.put(EDConfig.api(`/forum/${this.state.post_id}`), data);

        promise.then(this.onSubmitted.bind(this))
            .then(() => {
                // empty the text field once the comments were saved.
                this.setState({
                    comments: '',
                    post_id: 0,
                    show_reply: false
                });
            })
    }

    onSubmitted(response) {
        this.load();
    }

    onLikeClick(ev, postId, liked) {
        ev.preventDefault();

        if (! this.props.accountId) {
            this.onUnauthenticated(ev.target);
            return;
        }

        if (! liked) {
            axios.post(EDConfig.api(`/forum/like/${postId}`)).then(this.onLiked.bind(this, postId));
        } else {
            axios.delete(EDConfig.api(`/forum/like/${postId}`)).then(this.onUnliked.bind(this, postId));
        }
    }

    onLiked(postId, response) {
        const posts = this.state.posts;
        let post = posts.find(p => p.id === postId);
        if (post) {
            post.likes.push({ forum_post_id: postId, account_id: this.props.accountId });

            if (response.status === 201) {
                post.number_of_likes += 1;
            }

            this.setState({ posts });
        }
    }

    onUnliked(postId, response) {
        const posts = this.state.posts;
        let post = posts.find(p => p.id === postId && p.account_id === this.props.accountId);
        if (post) {
            post.likes = [];

            if (response.status === 201) {
                post.number_of_likes -= 1;
            }

            this.setState({ posts });
        }
    }

    onUnauthenticated(target) {
        const messageContainer = document.getElementById('forum-log-in-box');
        if (! messageContainer) {
            this.login();
        }

        target.classList.add('unauthorized-animation');

        window.setTimeout(() => {
            messageContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            messageContainer.classList.add('unauthorized-animation');
        }, 500);
    }

    onLoginClick(ev) {
        ev.preventDefault();
        this.login();
    }

    onEditPost(post, ev) {
        ev.preventDefault();

        axios.get(EDConfig.api(`/forum/${post.id}/edit`))
            .then(this.onEditPostDataReceived.bind(this));
    }

    onEditPostDataReceived(resp) {
        this.setState({
            comments: resp.data.content,
            post_id: resp.data.id,
            show_reply: true
        });

        if (this.textboxContainer) {
            this.textboxContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    onPostEdited(id, response) {
        this.load();
    }

    onDiscardChanges() {
        this.setState({
            post_id: 0,
            comments: '',
            show_reply: false
        });
    }

    onDeletePost(post, ev) {
        ev.preventDefault();

        if (! confirm('Are you sure you want to delete your post?')) {
            return;
        }

        axios.delete(EDConfig.api(`/forum/${post.id}`))
            .then(this.onPostDeleted.bind(this));
    }

    onPostDeleted() {
        this.load();
    }

    onReplyClick() {
        this.setState({
            show_reply: true, 
            highlighted_post_id: 0
        });
    }

    onPaginationClick(pageNo) {
        this.setState({
            highlighted_post_id: 0
        });

        this.load(pageNo);
    }

    renderUnauthorized() {
        return <div className="alert alert-info" id="forum-log-in-box">
            <strong>
                <span className="glyphicon glyphicon-info-sign" />
                {' '}
                { ! Array.isArray(this.state.posts) || this.state.posts.length < 1
                    ? 'Would you like to be the first to comment?'
                    : 'Would you like to share your thoughts on the discussion?' 
                }
            </strong>
            {' '}
            <a href="#" onClick={this.onLoginClick.bind(this)}>
                Log in to create a profile
            </a>.
        </div> ;
    }

    renderTextbox() {
        return <div>
            <div className={classNames('text-right', {'hidden': this.state.show_reply})}>
                <button className="btn btn-primary" onClick={this.onReplyClick.bind(this)}>
                    <span className="glyphicon glyphicon-pencil" />
                    {' '}
                    Write a comment
                </button>
            </div>
            <div className={classNames({'hidden': ! this.state.show_reply})} ref={elem => this.textboxContainer = elem}>
                {this.state.post_id ? <p><span className="glyphicon glyphicon-info-sign" /> Editing your comment ({this.state.post_id}):</p> : ''}
                <form onSubmit={this.onSubmit.bind(this)}>
                    <div className="form-group">
                        <textarea className="form-control" placeholder="Your comments ..." name="comments" value={this.state.comments} required={true}
                            onChange={super.onChange.bind(this)} rows={5} />
                    </div>
                    <div className="form-group text-right">
                        <button type="button" className="btn btn-default" onClick={this.onDiscardChanges.bind(this)}>
                            <span className="glyphicon glyphicon-remove" />
                            {' '}
                            {this.state.post_id ? 'Discard changes' : 'Cancel' }
                        </button> 
                        {' '}
                        <button type="submit" className="btn btn-primary">
                            <span className="glyphicon glyphicon-send" />
                            {' '}
                            {this.state.post_id ? 'Save changes' : 'Post'}
                        </button>
                    </div>
                </form>
            </div>
        </div>;
    }

    renderTools() {
        if (! this.props.enabled) {
            return undefined;
        }

        return <div>
            { ! this.isInfiniteScroll() && this.state.posts.length > 0 ? <hr /> : undefined }
            { this.props.accountId ? this.renderTextbox() : this.renderUnauthorized() }
            { this.isInfiniteScroll() && this.state.posts.length > 0 ? <hr /> : undefined }
        </div>;
    }

    renderPost(parser, post) {
        return <div key={post.id} className={classNames('forum-post', 
            {'highlight': this.state.highlighted_post_id === post.id})} id={`forum-post-${post.id}`}>
            <div className="post-profile-picture">
                <a href={`/author/${post.account_id}`} title={`View ${post.account.nickname}'s profile`}>
                    <img src={post.account.has_avatar 
                        ? `/storage/avatars/${post.account_id}.png` 
                        : '/img/anonymous-profile-picture.png'} />
                </a>
            </div>
            <div className="post-content">
                <div className="post-header">
                    <a href={`/author/${post.account_id}`} title={`View ${post.account.nickname}'s profile`} 
                        className="nickname">{post.account.nickname}</a>
                    {' '}
                    {post.account.tengwar ? <span className="tengwar">{post.account.tengwar}</span> : undefined}
                    {' · '}
                    {! post.is_deleted 
                        ? <span>
                            {`${post.number_of_likes || '0'} `}
                            <a href="#" onClick={ev => this.onLikeClick(ev, post.id, this.props.accountId && 
                            post.likes.length > 0)}>
                        <span className={classNames('glyphicon', 'glyphicon-thumbs-up', { 
                            'like-not-liked': ! this.props.accountId || post.likes.length === 0, 
                            'like-liked': this.props.accountId && post.likes.length > 0 
                        })} />
                        </a>
                        </span>
                    : undefined}
                    <span className="post-no">#{post.id}</span>
                </div>
                <div className="post-body">
                    { !post.is_deleted 
                        ? parser.parse(post.content)
                        : <em>{post.account.nickname} has redacted their comment.</em>
                    }
                </div>
                <div className="post-tools">
                    <span className="date">{ (new Date(post.created_at)).toLocaleString() }</span>
                    { ! post.is_deleted && this.props.accountId === post.account_id ?
                    <span className="tools">
                        <a href="#" onClick={this.onDeletePost.bind(this, post)}>Delete</a>
                        { ' · ' }
                        <a href="#" onClick={this.onEditPost.bind(this, post)}>Edit</a>
                    </span>
                : undefined }
                </div> 
            </div>
        </div>;
    }

    renderPagination() {
        const numberOfPages = this.state.pages; 
        const currentPage = this.state.major_id;
        const pages = [];

        if (numberOfPages < 2) {
            return undefined;
        }

        for (var i = 0; i < numberOfPages; i += 1) {
            pages[i] = i + 1;
        }

        return <nav className="text-center">
            <ul className="pagination">
                <li className={classNames({'disabled': currentPage === 1})}>
                    <a href="#" onClick={this.onPaginationClick.bind(this, currentPage - 1)}>
                        <span aria-hidden="true">&larr; Older</span>
                    </a>
                </li>
                { pages.map(pageNo => <li key={`p${pageNo}`} className={classNames({'active': pageNo === currentPage})}>
                    <a href="#" onClick={this.onPaginationClick.bind(this, pageNo)}>
                        {pageNo}
                    </a>
                </li>)}
                <li className={classNames({'disabled': currentPage === numberOfPages})}>
                    <a href="#" onClick={this.onPaginationClick.bind(this, currentPage + 1)}>
                        <span aria-hidden="true">Newer &rarr;</span>
                    </a>
                </li>
            </ul>
        </nav>;
    }

    render() {
        let parser = null;
        if (this.state.posts.length > 0) {
            parser = new HtmlToReactParser();
        }

        return <div>
            { this.isInfiniteScroll() ? this.renderTools() : undefined}
            <div ref={container => this.container = container}>
                { this.state.posts.map(this.renderPost.bind(this, parser)) }
            </div>
            { this.state.loading ? <div className="sk-spinner sk-spinner-pulse" /> : undefined}
            { this.isInfiniteScroll() ? undefined : 
                <div>
                    {this.renderPagination()}
                    {this.renderTools()}
                </div> 
            }
        </div>;
    }
}

EDComments.defaultProps = {
    morph: '',
    entityId: 0,
    accountId: 0,
    order: 'desc',
    majorId: 0,
    enabled: true
};

export default EDComments;
