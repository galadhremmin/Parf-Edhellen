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
            longDescription: undefined
        };

        this.previewStore = createStore(EDSentenceReducer, { 
            fragments: props.fragments 
        }, applyMiddleware(thunkMiddleware));
    }

    componentWillMount() {
        const longDescription = this.props.sentenceLongDescription;
        if (longDescription && !/^\s*$/.test(longDescription)) {
            axios.post(EDConfig.api('utility/markdown'), { markdown: longDescription})
                .then(this.onLongDescriptionReceive.bind(this));
        }
    }

    onLongDescriptionReceive(resp) {
        this.setState({
            longDescription: resp.data.html
        });
    }

    onPreviousClick(ev) {
        ev.preventDefault();
        this.props.history.goBack();
    }

    onSubmit(ev) {
        ev.preventDefault();
    }
 
    render() {
        let longDescription = undefined;
        if (this.state.longDescription) {
            const parser = new HtmlToReactParser();
            longDescription = parser.parse(this.state.longDescription);
        }
        
        return <div> 
            <div className="well">
                <h2>{this.props.sentenceName}</h2>
                <p>{this.props.sentenceDescription}</p>
                <Provider store={this.previewStore}>
                    <EDFragmentExplorer stateInUrl={false} />
                </Provider>
                <div>{longDescription ? longDescription : ''}</div>
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
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description
    };
};

export default withRouter(connect(mapStateToProps)(EDPreviewForm));
