import React from 'react';
import axios from 'axios';
import EDConfig from 'ed-config';
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
            entityId: this.props.entityId
        };
        
        axios.post(EDConfig.api('/forum'), data).then(this.onSubmitted.bind(this));
    }

    onSubmitted(response) {
        console.log(response.data);
    }

    render() {
        return <div>
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
            <div>
                { this.state.posts.map(c => <div>
                    
                </div>) }
            </div>
        </div>;
    }
}

EDComments.defaultProps = {
    context: '',
    entityId: 0
};

export default EDComments;
