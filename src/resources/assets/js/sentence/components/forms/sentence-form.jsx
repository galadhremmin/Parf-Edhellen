import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import EDAPI from 'ed-api';
import { EDStatefulFormComponent } from 'ed-form';
import { smoothScrollIntoView } from 'ed-scrolling';
import { setSentenceData } from '../../actions/admin';
import EDLanguageSelect from 'ed-components/language-select';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';
import EDAccountSelect from '../../../_shared/components/account-select';

class EDSentenceForm extends EDStatefulFormComponent {

    constructor(props) {
        super(props);

        this.state = {
            id: 0,
            account_id: 0,
            name: '',
            source: '',
            language_id: 0,
            is_neologism: false,
            description: '',
            long_description: '',
            account: undefined,
            errors: undefined,
            notes: ''
        };
    }

    componentDidMount() {
        this.setState({
            id: this.props.sentenceId,
            name: this.props.sentenceName,
            source: this.props.sentenceSource,
            language_id: this.props.sentenceLanguageId,
            description: this.props.sentenceDescription,
            long_description: this.props.sentenceLongDescription,
            is_neologism: this.props.sentenceIsNeologism,
            account_id: this.props.sentenceAccountId,
            notes: this.props.notes
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
            is_neologism: state.is_neologism,
            account_id: state.account_id || undefined,
            notes: state.notes,
            morph: this.props.admin ? undefined : 'sentence',
            contribution_id: this.props.contributionId || undefined
        };

        EDAPI.post(this.props.admin ? '/admin/sentence/validate'
            : '/dashboard/contribution/substep-validate', payload)
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
        if (request.response.status !== EDAPI.apiValidationErrorStatusCode) {
            return; 
        }

        // Laravel returns a dictionary with the name of the component as the key.
        // Flatten the errors array, by aggregating all validation errors. 
        const groupedErrors = request.response.data.errors;
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
        smoothScrollIntoView(this.formControl);
    }
 
    render() {
        return <form onSubmit={this.onSubmit.bind(this)} ref={c => this.formControl = c}>
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
            {this.props.admin ?
            <div className="form-group">
                <label htmlFor="ed-sentence-account" className="control-label">Account</label>
                <EDAccountSelect componentId="ed-sentence-account" componentName="account_id" required={true}
                    value={this.state.account_id} onChange={super.onChange.bind(this)} />
            </div> : undefined}
            <div className="form-group">
                <label htmlFor="ed-sentence-language" className="control-label">Language</label>
                <EDLanguageSelect className="form-control" componentId="ed-sentence-language" componentName="language_id"
                    onChange={ev => super.onChange(ev, 'number')} value={this.state.language_id} />
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
            {! this.props.admin ?
            <div className="form-group">
                <label htmlFor="ed-notes" className="control-label">Notes for reviewer</label>
                <textarea className="form-control" name="notes" id="ed-notes" rows={4}
                    value={this.state.notes} onChange={super.onChange.bind(this)} />
            </div> : undefined}
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
        sentenceAccountId: state.account_id,
        sentenceId: state.id,
        notes: state.notes,
        admin: state.is_admin,
        contributionId: state.contribution_id
    };
};

export default withRouter(connect(mapStateToProps)(EDSentenceForm));
