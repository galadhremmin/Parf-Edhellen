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
        // dirty deep copy to ensure loss of all references!
        const fragments = JSON.parse(JSON.stringify(this.props.fragments)); 

        // generate fake IDs
        fragments.forEach((f, i) => {
            f.id = -i - 1;
        });

        // build a request object for the markdown parser, which is passed to the 
        // server, before the store is established.
        const markdowns = {};

        const longDescription = this.props.sentenceLongDescription;
        if (longDescription && longDescription && !/^\s*$/.test(longDescription)) {
            markdowns['long_description'] = longDescription;
        }

        // identify fragments by index.
        for (let i = 0; i < fragments.length; i += 1) {
            const data = fragments[i];

            if (! data.interpunctuation && data.comments && !/^\s*$/.test(data.comments)) {
                markdowns['fragment-' + i] = data.comments;
            }
        }

        if (Object.keys(markdowns).length > 0) {
            axios.post(EDConfig.api('utility/markdown'), { markdowns })
                .then(resp => this.onHtmlReceive(resp, fragments));
        } else {
            this.createStore(fragments);
        }
    }

    createStore(fragments) {
        const state = { 
            fragments,
            latin: this.props.latin,
            tengwar: this.props.tengwar
        };
        
        this.previewStore = createStore(EDSentenceReducer, state, 
            applyMiddleware(thunkMiddleware));

        this.setState({
            loading: false
        });
    }

    onHtmlReceive(response, fragments) {
        const keys = Object.keys(response.data);

        for (let key of keys) {
            if (key === 'long_description') {
                this.setState({
                    longDescription: response.data[key]
                });
            } else if (key.substr(0, 9) === 'fragment-') {
                const index = parseInt(key.substr(9), 10);
                fragments[index].comments = response.data[key];
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
            account_id:       props.sentenceAccountId || undefined,
            name:             props.sentenceName,
            source:           props.sentenceSource,
            language_id:      props.sentenceLanguageId,
            description:      props.sentenceDescription,
            long_description: props.sentenceLongDescription,
            is_neologism:     props.sentenceIsNeologism,
            fragments:        props.fragments,
            morph:            'sentence'
        };

        if (payload.id) {
            axios.put(this.props.admin ? `/admin/sentence/${payload.id}` 
                : `/dashboard/contribution/${payload.id}`, payload)
                .then(this.onSavedResponse.bind(this), this.onFailedResponse.bind(this));
        } else {
            axios.post(this.props.admin ? '/admin/sentence' : '/dashboard/contribution', payload)
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
        latin: state.latin,
        tengwar: state.tengwar,
        sentenceName: state.name,
        sentenceSource: state.source,
        sentenceLanguageId: state.language_id,
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description,
        sentenceIsNeologism: state.is_neologism,
        sentenceAccountId: state.account_id,
        sentenceId: state.id,
        admin: state.is_admin
    };
};

export default withRouter(connect(mapStateToProps)(EDPreviewForm));
