import React from 'react';
import axios from 'axios';
import EDConfig from 'ed-config';
import classNames from 'classnames';
import { EDStatefulFormComponent } from 'ed-form';

class EDComments extends EDStatefulFormComponent {
    constructor(props) {
        super(props);

        this.state = {
            comments: '',
            posts: []
        };
    }

    componentDidMount() {
        this.load();
    }

    load(offset, parentPostId) {
        if (offset === undefined) {
            offset = 0;
        }

        const url = EDConfig.api(`/forum?context=${this.props.context}&entity_id=${this.props.entityId}`);
        axios.get(url).then(this.onLoaded.bind(this));
    }

    onLoaded(response) {
        this.setState({
            posts: response.data
        });
    }

    onSubmit(ev) {
        ev.preventDefault();

        const data = {
            comments: this.state.comments,
            context: this.props.context,
            entity_id: this.props.entityId
        };
        
        axios.post(EDConfig.api('/forum'), data).then(this.onSubmitted.bind(this));
    }

    onSubmitted(response) {
        this.load();
    }

    onLikeClick(ev, postId, liked) {
        ev.preventDefault();

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

    render() {
        return <div>
            <div>
                { this.state.posts.map(c => <div key={c.id} className="forum-post">
                    <div className="post-profile-picture">
                        <a href={`/author/${c.account_id}`} title={`Visit ${c.account.nickname}'s profile`}>
                            <img src={c.account.has_avatar ? `/storage/avatars/${c.account_id}.png` : '/img/anonymous-profile-picture.png'} />
                        </a>
                    </div>
                    <div className="post-content">
                        <div className="post-tools">
                            <a href={`/author/${c.account_id}`} title={`Visit ${c.account.nickname}'s profile`} className="nickname">{c.account.nickname}</a>
                            { ' ' }
                            {c.account.tengwar ? <span className="tengwar">{c.account.tengwar}</span> : ''}
                            { ' · ' }
                            <span className="date">{ c.created_at }</span>

                            <span className="pull-right">
                                {! c.number_of_likes ? '' : `${c.number_of_likes} `}
                                <a href="#" onClick={ev => this.onLikeClick(ev, c.id, c.likes.length > 0)}>
                                    <span className={classNames('glyphicon', 'glyphicon-thumbs-up', { 'like-not-liked': c.likes.length === 0, 'like-liked': c.likes.length > 0 })} />
                                </a>
                            </span>
                        </div>
                        <div className="post-body">
                            { c.content }
                        </div>
                    </div>
                </div>) }
            </div>
            <hr />
            <div>
                <form onSubmit={this.onSubmit.bind(this)}>
                    <div className="form-group">
                        <textarea className="form-control" placeholder="Your comments ..." name="comments" value={this.state.comments}
                            onChange={super.onChange.bind(this)} />
                    </div>
                    <div className="form-group text-right">
                        <button type="submit" className="btn btn-primary">Publish comments</button>
                    </div>
                </form>
            </div>
        </div>;
    }
}

EDComments.defaultProps = {
    context: '',
    entityId: 0,
    accountId: 0
};

export default EDComments;
