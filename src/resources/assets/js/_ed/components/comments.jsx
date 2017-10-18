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
            jump_post_id,
            loading: false
        };

        enableSmoothScrolling();
    }

    componentWillMount() {
        window.addEventListener('scroll', this.onScroll.bind(this));
    }

    componentDidMount() {
        // Load comments if the client is specifically requesting to display them.
        if (this.state.jump_post_id) {
            this.load();
        } else {
            this.onScroll();
        }
    }

    login() {
        window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
    }

    load(fromId, parentPostId) {
        this.setState({
            loading: true
        });

        const url = EDConfig.api(
            `/forum?morph=${this.props.morph}&entity_id=${this.props.entityId}&order=${this.props.order}` + 
            (fromId ? `&from_id=${fromId}` : '')
        );
        
        return axios.get(url).then(this.onLoaded.bind(this, fromId || 0));
    }

    onLoaded(fromId, response) {
        const jumpPostId = this.state.jump_post_id;

        let posts = this.state.posts || [];
        const newPosts = response.data.posts || [];

        if (fromId === 0) {
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
            posts,
            jump_post_id: 0,
            loading: false
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
                    post_id: 0
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
            post_id: resp.data.id
        });
    }

    onPostEdited(id, response) {
        this.load();
    }

    onDiscardChanges() {
        this.setState({
            post_id: 0,
            comments: ''
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

    renderTools() {
        return <div>
            { this.props.order === 'asc' && this.state.posts.length > 0 && this.props.enabled ? <hr /> : '' }
            { this.props.accountId && this.props.enabled ? <div>
                {this.state.post_id ? <p><span className="glyphicon glyphicon-info-sign" /> Editing your comment ({this.state.post_id}):</p> : ''}
                <form onSubmit={this.onSubmit.bind(this)}>
                    <div className="form-group">
                        <textarea className="form-control" placeholder="Your comments ..." name="comments" value={this.state.comments} required={true}
                            onChange={super.onChange.bind(this)} rows={5} />
                    </div>
                    <div className="form-group text-right">
                        {this.state.post_id 
                            ? <button type="button" className="btn btn-default" onClick={this.onDiscardChanges.bind(this)}>
                            <span className="glyphicon glyphicon-remove" />
                            {' '}
                            Discard changes
                        </button> : ''}
                        {' '}
                        <button type="submit" className="btn btn-primary">
                            <span className="glyphicon glyphicon-send" />
                            {' '}
                            {this.state.post_id ? 'Save changes' : 'Post'}
                        </button>
                    </div>
                </form>
            </div> : (this.props.enabled 
                ? <div className="alert alert-info" id="forum-log-in-box">
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
                </div> 
                : '') }
            { this.props.order === 'desc' && this.state.posts.length > 0 && this.props.enabled ? <hr /> : '' }
        </div>;
    }

    render() {
        let parser = null;
        if (this.state.posts.length > 0) {
            parser = new HtmlToReactParser();
        }

        return <div>
            {this.props.order === 'desc' ? this.renderTools() : undefined}
            <div ref={container => this.container = container}>
                { this.state.posts.map(c => <div key={c.id} className="forum-post" id={`forum-post-${c.id}`}>
                    <div className="post-profile-picture">
                        <a href={`/author/${c.account_id}`} title={`Visit ${c.account.nickname}'s profile`}>
                            <img src={c.account.has_avatar ? `/storage/avatars/${c.account_id}.png` : '/img/anonymous-profile-picture.png'} />
                        </a>
                    </div>
                    <div className="post-content">
                        <div className="post-tools">
                            <a href={`/author/${c.account_id}`} title={`Visit ${c.account.nickname}'s profile`} className="nickname">{c.account.nickname}</a>
                            {' '}
                            {c.account.tengwar ? <span className="tengwar">{c.account.tengwar}</span> : ''}
                            {' · '}
                            <span className="date">{ c.created_at }</span>
                            {' '}
                            <span className="post-id">#{c.id}</span>
                            <span className="pull-right">
                                {c.is_deleted || ! c.number_of_likes ? '' : `${c.number_of_likes} `}
                                {! c.is_deleted 
                                    ? <a href="#" onClick={ev => this.onLikeClick(ev, c.id, this.props.accountId && c.likes.length > 0)}>
                                    <span className={classNames('glyphicon', 'glyphicon-thumbs-up', { 
                                        'like-not-liked': ! this.props.accountId || c.likes.length === 0, 
                                        'like-liked': this.props.accountId && c.likes.length > 0 
                                    })} />
                                    </a>
                                : ''}
                            </span>
                        </div>
                        <div className="post-body">
                            { !c.is_deleted 
                                ? parser.parse(c.content)
                                : <em>{c.account.nickname} has redacted this post.</em>
                            }
                        </div>
                        { ! c.is_deleted && this.props.accountId === c.account_id ?
                        <div className="small pull-right">
                            <a href="#" onClick={this.onDeletePost.bind(this, c)}>Delete</a>
                            { ' · ' }
                            <a href="#" onClick={this.onEditPost.bind(this, c)}>Edit</a>
                        </div> : '' }
                    </div>
                </div>) }
            </div>
            {this.state.loading ? <div className="sk-spinner sk-spinner-pulse" /> : undefined}
            {this.props.order === 'asc' ? this.renderTools() : undefined}
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