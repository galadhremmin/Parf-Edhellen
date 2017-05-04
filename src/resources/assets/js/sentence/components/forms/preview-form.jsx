import React from 'react';
import { withRouter } from 'react-router';
import { createStore, applyMiddleware } from 'redux';
import { Provider, connect } from 'react-redux';
import thunkMiddleware from 'redux-thunk';
import classNames from 'classnames';
import axios from 'axios';
import { Parser as HtmlToReactParser } from 'html-to-react';
import EDConfig from 'ed-config';
import EDErrorList from 'ed-components/error-list';
import EDSentenceReducer from '../../reducers';
import EDFragmentExplorer from '../fragment-explorer';

class EDPreviewForm extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            longDescription: undefined,
            loading: true,
            errors: undefined
        };
    }

    componentWillMount() {
        const markdowns = {};

        const longDescription = this.props.sentenceLongDescription;
        if (longDescription && !/^\s*$/.test(longDescription)) {
            markdowns['long_description'] = longDescription;
        }

        for (let i = 0; i < this.props.fragments.length; i += 1) {
            const data = this.props.fragments[i];

            if (! data.interpunctuation && !/^\s*$/.test(data.comments)) {
                markdowns['fragment-' + i] = data.comments;
            }
        }

        if (Object.keys(markdowns).length > 0) {
            axios.post(EDConfig.api('utility/markdown'), { markdowns })
                .then(this.onHtmlReceive.bind(this));
        } else {
            this.createStore(this.props.fragments);
        }
    }

    createStore(fragments) {
        this.previewStore = createStore(EDSentenceReducer, { fragments }, 
            applyMiddleware(thunkMiddleware));

        this.setState({
            loading: false
        });
    }

    onHtmlReceive(resp) {
        // dirty deep copy to ensure loss of all references!
        const fragments = JSON.parse(JSON.stringify(this.props.fragments)); 
        const keys = Object.keys(resp.data);

        for (let key of keys) {
            if (key === 'long_description') {
                this.setState({
                    longDescription: resp.data[key]
                });
            } else if (key.substr(0, 9) === 'fragment-') {
                const index = parseInt(key.substr(9), 10);
                fragments[index].comments = resp.data[key];
            }
        }

        this.createStore(fragments);
    }

    onPreviousClick(ev) {
        ev.preventDefault();
        this.props.history.goBack();
    }

    onSubmit(ev) {
        ev.preventDefault();

        const props = this.props;
        const payload = {
            id:               props.sentenceId || undefined,
            name:             props.sentenceName,
            source:           props.sentenceSource,
            language_id:      props.sentenceLanguageId,
            description:      props.sentenceDescription,
            long_description: props.sentenceLongDescription,
            is_neologism:     props.sentenceIsNeologism,
            fragments:        props.fragments
        };

        if (payload.id) {
            axios.put(`/admin/sentence/${payload.id}`, payload)
                .then(this.onSavedResponse.bind(this), this.onFailedResponse.bind(this));
        } else {
            axios.post('/admin/sentence', payload)
                .then(this.onSavedResponse.bind(this), this.onFailedResponse.bind(this));
        }
    }

    onSavedResponse(request) {
        window.location.href = request.data.url;
    }

    onFailedResponse(request) {
        let errors;
        if (request.response.status !== EDConfig.apiValidationErrorStatusCode) {
            errors = ['Failed to save your phrase due to a server error.']; 
        } else {
            errors = ['Your phrase cannot be saved because validation fails. Please go to the previous steps and try again.'];
        }

        this.setState({
            errors
        });
    }
 
    render() {
        let longDescription = undefined;
        if (this.state.longDescription) {
            const parser = new HtmlToReactParser();
            longDescription = parser.parse(this.state.longDescription);
        }
        
        return <div> 
            <EDErrorList errors={this.state.errors} />
            <div className="well">
                <h2>{this.props.sentenceName}</h2>
                <p>{this.props.sentenceDescription}</p>
                {this.state.loading 
                    ? <div className="sk-spinner sk-spinner-pulse"></div>
                    : <Provider store={this.previewStore}>
                        <EDFragmentExplorer stateInUrl={false} />
                    </Provider>}
                {longDescription ? <div className="ed-fragment-long-description">{longDescription}</div> : ''}
            </div>
            <nav>
                <ul className="pager">
                    <li className="previous"><a href="#" onClick={this.onPreviousClick.bind(this)}>&larr; Previous step</a></li>
                    <li className="next">
                        <a href="#" onClick={this.onSubmit.bind(this)}>
                            Confirm and save
                            &nbsp;
                            &nbsp;
                            <span className="glyphicon glyphicon-save"></span></a>
                    </li>
                </ul>
            </nav>
        </div>;
    }
}

const mapStateToProps = state => {
    return {
        fragments: state.fragments,
        sentenceName: state.name,
        sentenceSource: state.source,
        sentenceLanguageId: state.language_id,
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description,
        sentenceIsNeologism: state.is_neologism,
        sentenceId: state.id
    };
};

export default withRouter(connect(mapStateToProps)(EDPreviewForm));
