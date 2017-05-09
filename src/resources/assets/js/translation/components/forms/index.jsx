import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import axios from 'axios';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import EDConfig from 'ed-config';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';
import EDAccountSelect from '../../../_shared/components/account-select';

class EDTranslationForm extends EDStatefulFormComponent {

    constructor(props) {
        super(props);

        this.state = {
            id: 0,
            account_id: 0,
            language_id: 0,
            word_id: 0,
            word: '',
            translation: '',
            source: '',
            comments: '',
            is_uncertain: false
        };

        enableSmoothScrolling();
    }

    componentDidMount() {
        const props = this.props;
        this.setState({
            id:            props.translationId,
            account_id:    props.translationAccountId,
            language_id:   props.translationLanguageId,
            word_id:       props.translationWordId,
            word:          props.translationWord,
            translation:   props.translation,
            source:        props.transationSource,
            comments:      props.translationComments,
            is_uncertain:  props.translationUncertain
        })
    }

    onSubmit(ev) {
        ev.preventDefault();

        const state = this.state;
        const payload = {
            id: state.id
        };

        let promise;
        if (payload.id) {
            promise = axios.put(`/admin/translate/${payload.id}`, payload);
        } else {
            promise = axios.post('/admin/translate', payload);
        }

        promise.then(request => this.onValidateSuccess(request, payload),
                     request => this.onValidateFail(request, payload));
    }

    onValidateSuccess(request, payload) {
        this.setState({
            errors: undefined
        });

        // success!
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
                Please be as thorough as possible.
            </p>
            <div className="form-group">
                <label htmlFor="ed-translation-gloss" className="control-label">Gloss</label>
                <input type="text" className="form-control" id="ed-translation-gloss" name="word" 
                    value={this.state.word} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-translation" className="control-label">Translation</label>
                <input type="text" className="form-control" id="ed-translation-translation" name="translation" 
                    value={this.state.translation} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-source" className="control-label">Source</label>
                <input type="text" className="form-control" id="ed-translation-source" name="source" 
                    value={this.state.source} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-account" className="control-label">Account</label>
                <EDAccountSelect componentId="ed-translation-account" componentName="account_id" 
                    value={this.state.account_id} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-language" className="control-label">Language</label>
                <select className="form-control" id="ed-translation-language" name="language_id" 
                    onChange={ev => super.onChange(ev, 'number')} value={this.state.language_id}>
                    <option value="0"></option>
                    {this.props.languages
                        .filter(l => l.is_invented)
                        .map(l => <option value={l.id} key={l.id}>{l.name}</option>)}
                </select>
            </div>
            <div className="checkbox">
                <label>
                    <input type="checkbox" name="is_uncertain"
                        checked={this.state.is_uncertain} onChange={super.onChange.bind(this)} />
                        Uncertain
                </label>
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-comments" className="control-label">Comments</label>
                <EDMarkdownEditor componentId="ed-translation-comments" componentName="comments" rows={8}
                    value={this.state.comments} onChange={super.onChange.bind(this)} />
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
        translationId:         state.id,
        translationAccountId:  state.account_id,
        translationLanguageId: state.language_id,
        translationWordId:     state.word_id,
        translationWord:       state.word.word,
        translation:           state.translation,
        transationSource:      state.source,
        translationComments:   state.comments,
        translationUncertain:  state.is_uncertain,
        languages:             state.languages
    };
};


export default connect(mapStateToProps)(EDTranslationForm);
