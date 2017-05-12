import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import axios from 'axios';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import EDConfig from 'ed-config';
import { requestTranslationGroups } from '../../actions/admin';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';
import EDWordSelect from '../../../_shared/components/word-select';
import EDAccountSelect from '../../../_shared/components/account-select';
import EDSpeechSelect from '../../../_shared/components/speech-select';
import EDTengwarInput from '../../../_shared/components/tengwar-input';

class EDTranslationForm extends EDStatefulFormComponent {

    constructor(props) {
        super(props);

        this.state = {
            id: 0,
            account_id: 0,
            language_id: 0,
            word_id: 0,
            translation_group_id: 0,
            speech_id: 0,
            word: '',
            translation: '',
            source: '',
            tengwar: '',
            comments: '',
            sense: undefined,
            keywords: [],
            is_uncertain: false,
            is_rejected: false
        };

        enableSmoothScrolling();
    }

    componentWillMount() {
        this.props.dispatch(requestTranslationGroups());
    }

    componentDidMount() {
        const props = this.props;
        this.setState({
            id:                   props.translationId || 0,
            account_id:           props.translationAccountId || 0,
            language_id:          props.translationLanguageId || 0,
            word_id:              props.translationWordId || 0 ,
            speech_id:            props.translationSpeechId || 0,
            translation_group_id: props.translationGroupId || 0,
            sense:                props.translationSense || '',
            keywords:             props.translationKeywords || [],
            translation:          props.translation || '',
            source:               props.transationSource || '',
            comments:             props.translationComments || '',
            is_uncertain:         props.translationUncertain || 0,
            is_rejected:          props.translationRejected || 0,
            tengwar:              props.translationTengwar || '',
            word:                 props.translationWord ? props.translationWord.word : '', 
        })
    }

    onSubmit(ev) {
        ev.preventDefault();

        const state = this.state;
        const payload = {
            ...state,
            // optional parameters beneath
            id:                   state.id || undefined,
            tengwar:              state.tengwar.length > 0 ? state.tengwar : undefined,
            translation_group_id: state.translation_group_id || undefined,
        };

        let promise;
        if (payload.id) {
            promise = axios.put(`/admin/translation/${payload.id}`, payload);
        } else {
            promise = axios.post('/admin/translation', payload);
        }

        promise.then(request => this.onValidateSuccess(request, payload),
                     request => this.onValidateFail(request, payload));
    }

    onValidateSuccess(request, payload) {
        this.setState({
            errors: undefined
        });

        window.location.href = request.data.url;
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
        if (this.props.loading) {
            return <div className="sk-spinner sk-spinner-pulse"></div>;
        }

        const language = this.props.languages.find(l => l.id === this.state.language_id);

        return <form onSubmit={this.onSubmit.bind(this)}>
            <EDErrorList errors={this.state.errors} />
            <p>
                <span className="glyphicon glyphicon-info-sign" />{' '}
                The <em>gloss</em> is the English translation of the <em>word</em>, whereas the <em>sense</em>{' '}
                is a logical grouping for the word, and should also be in English. A typical sense for the word "elm" {' '}
                would be "tree". Keywords are additional words associated with the sense. Make sure you understand {' '}
                all these fields before saving your changes.
            </p>
            <p>
                Please be as thorough as possible, and make sure to <em>always include sources!</em>
            </p>
            <div className="form-group">
                <label htmlFor="ed-translation-word" className="control-label">Word</label>
                <input type="text" className="form-control" id="ed-translation-word" name="word" 
                    value={this.state.word} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-tengwar" className="control-label">Tengwar</label>
                <EDTengwarInput componentId="ed-translation-tengwar" componentName="tengwar" 
                    tengwarMode={language ? language.tengwar_mode : undefined} transcriptionSubject={this.state.word}
                    value={this.state.tengwar} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-translation" className="control-label">Gloss</label>
                <input type="text" className="form-control" id="ed-translation-translation" name="translation" 
                    value={this.state.translation} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-sense" className="control-label">Sense</label>
                <EDWordSelect multiple={false} componentId="ed-translation-sense" componentName="sense" 
                    isSense={true} required={true} canCreateNew={true}
                    value={this.state.sense} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-keywords" className="control-label">Keywords</label>
                <EDWordSelect multiple={true} componentId="ed-translation-keywords" componentName="keywords" 
                    isSense={false} canCreateNew={true}
                    value={this.state.keywords} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-speech" className="control-label">Type of speech</label>
                <EDSpeechSelect componentId="ed-translation-speech" componentName="speech_id"
                    value={this.state.speech_id} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-source" className="control-label">Source</label>
                <input type="text" className="form-control" id="ed-translation-source" name="source" 
                    value={this.state.source} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-group" className="control-label">Group</label>
                <select name="translation_group_id" id="ed-translation-group" className="form-control"
                    value={this.state.translation_group_id} onChange={super.onChange.bind(this)}>
                    <option value="0"></option>
                    {this.props.groups.map(g => <option key={g.id} value={g.id}>{g.name}</option>)}
                </select>
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-account" className="control-label">Account</label>
                <EDAccountSelect componentId="ed-translation-account" componentName="account_id" 
                    value={this.state.account_id} onChange={super.onChange.bind(this)} required={true} />
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
                        Uncertain or a neologism
                </label>
            </div>
            <div className="checkbox">
                <label>
                    <input type="checkbox" name="is_rejected"
                        checked={this.state.is_rejected} onChange={super.onChange.bind(this)} />
                        Rejected
                </label>
            </div>
            <div className="form-group">
                <label htmlFor="ed-translation-comments" className="control-label">Comments</label>
                <EDMarkdownEditor componentId="ed-translation-comments" componentName="comments" rows={8}
                    value={this.state.comments} onChange={super.onChange.bind(this)} />
            </div>
            <p className="alert alert-info">
                <strong>Important!</strong> Please <em>only</em> confirm your changes <em>if they are worth saving,</em>{' '}
                because a lot of things happen under the hood when  you press that button. To undo your changes, please press the {' '}
                <em>cancel</em>-button or reload the page. 
            </p>
            <nav>
                <ul className="pager">
                    <li className="previous">
                        <a href="/admin/translation">
                            <span className="glyphicon glyphicon-remove"></span>
                            {' '}
                            Cancel
                        </a>
                    </li>
                    <li className="next">
                        <a href="#" onClick={this.onSubmit.bind(this)}>
                            Confirm and save
                            &nbsp;
                            &nbsp;
                            <span className="glyphicon glyphicon-save"></span></a>
                    </li>
                </ul>
            </nav>
        </form>;
    }
}

EDTranslationForm.defaultProps = {
    loading: true
};

const mapStateToProps = state => {
    return {
        translationId:         state.id,
        translationAccountId:  state.account_id,
        translationLanguageId: state.language_id,
        translationWordId:     state.word_id,
        translationSpeechId:   state.speech_id,
        translationWord:       state.word,
        translationTengwar:    state.tengwar,
        translationSense:      state.sense,
        translation:           state.translation,
        transationSource:      state.source,
        translationComments:   state.comments,
        translationUncertain:  state.is_uncertain,
        translationRejected:   state.is_rejected,
        translationKeywords:   state._keywords,
        translationGroupId:    state.translation_group_id,
        languages:             state.languages,
        groups:                state.groups,
        loading:               state.loading,
    };
};


export default connect(mapStateToProps)(EDTranslationForm);
