import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import axios from 'axios';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { setSentenceData } from '../../actions/admin';
import EDConfig from 'ed-config';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';

class EDSentenceForm extends EDStatefulFormComponent {

    constructor(props) {
        super(props);

        this.state = {
            name: '',
            source: '',
            language_id: 0,
            is_neologism: false,
            description: '',
            long_description: '',
            errors: undefined
        };

        enableSmoothScrolling();
    }

    componentDidMount() {
        this.setState({
            id: this.props.sentenceId,
            name: this.props.sentenceName,
            source: this.props.sentenceSource,
            language_id: this.props.sentenceLanguageId,
            description: this.props.sentenceDescription,
            long_description: this.props.sentenceLongDescription,
            is_neologism: this.props.sentenceIsNeologism
        });
    }

    onSubmit(ev) {
        ev.preventDefault();

        const state = this.state;
        const payload = {
            id: state.id || undefined,
            name: state.name,
            source: state.source,
            language_id: state.language_id,
            description: state.description,
            long_description: state.long_description,
            is_neologism: state.is_neologism
        };

        axios.post('/admin/sentence/validate', payload)
            .then(request => this.onValidateSuccess(request, payload),
                  request => this.onValidateFail(request, payload));
    }

    onValidateSuccess(request, payload) {
        this.setState({
            errors: undefined
        });

        // Make the changes permanent (in the client) by dispatching them on to Redux.
        this.props.dispatch(setSentenceData(payload));

        // Move forward to the next step
        this.props.history.goForward();
    }

    onValidateFail(request, payload) {
        // Laravel returns 422 when the request fails validation. In the event that
        // we received an alternate status code, bail, as we do not know what that payload
        // contains.
        if (request.response.status !== EDConfig.apiValidationErrorStatusCode) {
            return; 
        }

        // Laravel returns a dictionary with the name of the component as the key.
        // Flatten the errors array, by aggregating all validation errors. 
        const groupedErrors = request.response.data;
        const componentNames = Object.keys(groupedErrors);
        let aggregatedErrors = [];

        for (let componentName of componentNames) {
            aggregatedErrors = [...aggregatedErrors, ...groupedErrors[componentName]];
        }

        this.setState({
            errors: aggregatedErrors
        });

        // Scroll to the top of the page in the event that the client might have
        // scrolled too far down to notice the error messages.
        window.scroll({
            top: 0,
            behavior: 'smooth'
        });
    }
 
    render() {
        return <form onSubmit={this.onSubmit.bind(this)}>
            <EDErrorList errors={this.state.errors} />
            <p>
                This is the first step of three steps.
                Please provide some information about about your phrase. 
                You will specify the phrase itself on the next step.
                Longer texts such as poetry, letters, texts, etcetera are also supported.
            </p>
            <div className="form-group">
                <label htmlFor="ed-sentence-name" className="control-label">Title</label>
                <input type="text" className="form-control" id="ed-sentence-name" name="name" 
                    value={this.state.name} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-source" className="control-label">Source</label>
                <input type="text" className="form-control" id="ed-sentence-source" name="source" 
                    value={this.state.source} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-language" className="control-label">Language</label>
                <select className="form-control" id="ed-sentence-language" name="language_id" 
                    onChange={ev => super.onChange(ev, 'number')} value={this.state.language_id}>
                    <option value="0"></option>
                    {this.props.languages
                        .filter(l => l.is_invented)
                        .map(l => <option value={l.id} key={l.id}>{l.name}</option>)}
                </select>
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-description" className="control-label">Summary</label>
                <textarea id="ed-sentence-description" className="form-control" name="description" 
                    value={this.state.description} onChange={super.onChange.bind(this)}></textarea>
            </div>
            <div className="checkbox">
                <label>
                    <input type="checkbox" checked={this.state.is_neologism} name="is_neologism"
                        value={true} onChange={super.onChange.bind(this)} />
                        Neologism
                </label>
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-long-description" className="control-label">Description</label>
                <EDMarkdownEditor componentId="ed-sentence-long-description" componentName="long_description" rows={8}
                    value={this.state.long_description} onChange={super.onChange.bind(this)} />
            </div>
            <nav>
                <ul className="pager">
                    <li className="next"><a href="#" onClick={this.onSubmit.bind(this)}>Next step &rarr;</a></li>
                </ul>
            </nav>
        </form>;
    }
}

const mapStateToProps = state => {
    return {
        languages: state.languages,
        sentenceName: state.name,
        sentenceSource: state.source,
        sentenceLanguageId: state.language_id,
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description,
        sentenceIsNeologism: state.is_neologism,
        sentenceId: state.id
    };
};

export default withRouter(connect(mapStateToProps)(EDSentenceForm));
