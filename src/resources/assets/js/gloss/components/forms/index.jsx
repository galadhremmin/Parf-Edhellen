import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import EDAPI from 'ed-api';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDLanguageSelect from 'ed-components/language-select';
import EDErrorList from 'ed-components/error-list';
import { smoothScrollIntoView } from 'ed-scrolling';
import { requestGlossGroups, componentIsReady } from '../../actions/admin';
import EDTranslationSelect from '../../../_shared/components/translation-select';
import EDWordSelect from '../../../_shared/components/word-select';
import EDAccountSelect from '../../../_shared/components/account-select';
import EDSpeechSelect from '../../../_shared/components/speech-select';
import EDTengwarInput from '../../../_shared/components/tengwar-input';

class EDGlossForm extends EDStatefulFormComponent {

    constructor(props) {
        super(props);

        this.state = {
            id: 0,
            account_id: 0,
            language_id: 0,
            language: null,
            word_id: 0,
            gloss_group_id: 0,
            speech_id: 0,
            word: '',
            translations: '',
            source: '',
            tengwar: '',
            comments: '',
            notes: '',
            sense: undefined,
            keywords: [],
            is_uncertain: false,
            is_rejected: false
        };
    }

    componentWillMount() {
        EDAPI.languages().then(() => {
            this.props.dispatch(this.props.admin
                ? requestGlossGroups() // admin view requires information from the server
                : componentIsReady()
            );

            const props = this.props;
            this.setState({
                id:             props.glossId || 0,
                account_id:     props.glossAccountId || 0,
                language_id:    props.glossLanguageId || 0,
                word_id:        props.glossWordId || 0 ,
                speech_id:      props.glossSpeechId || 0,
                gloss_group_id: props.glossGroupId || 0,
                sense:          props.glossSense || '',
                keywords:       props.glossKeywords || [],
                translations:   props.glossTranslations || '',
                source:         props.transationSource || '',
                comments:       props.glossComments || '',
                notes:          props.glossNotes || '',
                is_uncertain:   props.glossUncertain || 0,
                is_rejected:    props.glossRejected || 0,
                tengwar:        props.glossTengwar || '',
                word:           props.glossWord ? props.glossWord.word : '', 
            })
        });
    }

    componentWillReceiveProps(props) {
        if (props.glossLanguageId) {
            EDAPI.languages(props.glossLanguageId).then(language => {
                this.setState({
                    language 
                });
            });
        }
    }

    onSubmit(ev) {
        ev.preventDefault();

        const state = this.state;
        const payload = {
            ...state,
            id:       state.id || undefined,
            tengwar:  state.tengwar.length > 0 ? state.tengwar : undefined,
            morph:    'gloss'
        };

        let promise;
        if (this.props.admin) {
            // optional parameter 
            payload.gloss_group_id = state.gloss_group_id || undefined;
            
            if (payload.id) {
                promise = EDAPI.put(`/admin/gloss/${payload.id}`, payload);
            } else {
                promise = EDAPI.post('/admin/gloss', payload);
            }
        } else {
            if (this.props.contributionId) {
                promise = EDAPI.put(`/dashboard/contribution/${this.props.contributionId}`, payload);
            } else {
                promise = EDAPI.post('/dashboard/contribution', payload);
            }
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

    onLanguageChange(ev) {
        const languageId = parseInt(ev.target.getValue(), 10);
        if (languageId === this.state.language_id) {
            return;
        }
        
        EDAPI.languages(languageId)
            .then(language => {
                super.onChange(ev, 'number');
                this.setState({
                    language
                });
            });
    }
 
    render() {
        if (this.props.loading) {
            return <div className="sk-spinner sk-spinner-pulse"></div>;
        }

        return <form onSubmit={this.onSubmit.bind(this)} ref={c => this.formControl = c}>
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
                <label htmlFor="ed-gloss-language" className="control-label">Language</label>
                <EDLanguageSelect className="form-control" componentId="ed-gloss-language" componentName="language_id" 
                    onChange={this.onLanguageChange.bind(this)} value={this.state.language_id} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-word" className="control-label">Word</label>
                <input type="text" className="form-control" id="ed-gloss-word" name="word" 
                    value={this.state.word} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-tengwar" className="control-label">Tengwar</label>
                <EDTengwarInput componentId="ed-gloss-tengwar" componentName="tengwar" 
                    tengwarMode={this.state.language ? this.state.language.tengwar_mode : undefined} transcriptionSubject={this.state.word}
                    value={this.state.tengwar} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-translations" className="control-label">
                    {this.state.translations.length > 1 ? 'Glosses' : 'Gloss'}
                </label>
                <EDTranslationSelect componentId="ed-gloss-translations" componentName="translations"
                    value={this.state.translations} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-sense" className="control-label">Sense</label>
                <EDWordSelect multiple={false} componentId="ed-gloss-sense" componentName="sense" 
                    isSense={true} required={true} canCreateNew={true}
                    value={this.state.sense} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-keywords" className="control-label">Keywords</label>
                <EDWordSelect multiple={true} componentId="ed-gloss-keywords" componentName="keywords" 
                    isSense={false} canCreateNew={true}
                    value={this.state.keywords} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-speech" className="control-label">Type of speech</label>
                <EDSpeechSelect componentId="ed-gloss-speech" componentName="speech_id"
                    value={this.state.speech_id} onChange={super.onChange.bind(this)} />
            </div>
            <div className="form-group">
                <label htmlFor="ed-gloss-source" className="control-label">Source</label>
                <input type="text" className="form-control" id="ed-gloss-source" name="source" 
                    value={this.state.source} onChange={super.onChange.bind(this)} />
            </div>
            {this.props.admin ?
            <div className="form-group">
                <label htmlFor="ed-gloss-group" className="control-label">Group</label>
                <select name="gloss_group_id" id="ed-gloss-group" className="form-control"
                    value={this.state.gloss_group_id} onChange={super.onChange.bind(this)}>
                    <option value="0"></option>
                    {this.props.groups.map(g => <option key={g.id} value={g.id}>{g.name}</option>)}
                </select>
            </div> : ''}
            {this.props.admin ?
            <div className="form-group">
                <label htmlFor="ed-gloss-account" className="control-label">Account</label>
                <EDAccountSelect componentId="ed-gloss-account" componentName="account_id" 
                    value={this.state.account_id} onChange={super.onChange.bind(this)} required={true} />
            </div> : ''}
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
                <label htmlFor="ed-gloss-comments" className="control-label">Comments</label>
                <EDMarkdownEditor componentId="ed-gloss-comments" componentName="comments" rows={8}
                    value={this.state.comments} onChange={super.onChange.bind(this)} />
            </div>
            {! this.props.admin ?
            <div className="form-group">
                <label htmlFor="ed-notes" className="control-label">Notes for reviewer</label>
                <textarea className="form-control" name="notes" id="ed-notes" rows={4}
                    value={this.state.notes} onChange={super.onChange.bind(this)} />
            </div> : ''}
            <p className="alert alert-info">
                <strong>Important!</strong> Please <em>only</em> confirm your changes <em>if they are worth saving,</em>{' '}
                because a lot of things happen under the hood when  you press that button. To undo your changes, please press the {' '}
                <em>cancel</em>-button or reload the page. 
            </p>
            <nav>
                <ul className="pager">
                    <li className="previous">
                        <a href={this.props.admin ? '/admin/gloss' : '/dashboard/contribution'}>
                            <span className="glyphicon glyphicon-remove"></span>
                            {' '}
                            Cancel
                        </a>
                    </li>
                    <li className="next">
                        <a href="#" onClick={this.onSubmit.bind(this)}>
                            {this.props.confirmButtonText}
                            &nbsp;
                            &nbsp;
                            <span className="glyphicon glyphicon-save"></span></a>
                    </li>
                </ul>
            </nav>
        </form>;
    }
}

EDGlossForm.defaultProps = {
    loading: true,
    admin: false,
    confirmButtonText: 'Confirm and save'
};

const mapStateToProps = state => {
    return {
        glossId:           state.id,
        glossAccountId:    state.account_id,
        glossLanguageId:   state.language_id,
        glossWordId:       state.word_id,
        glossSpeechId:     state.speech_id,
        glossWord:         state.word,
        glossTengwar:      state.tengwar,
        glossSense:        state.sense,
        glossTranslations: state.translations,
        transationSource:  state.source,
        glossComments:     state.comments,
        glossNotes:        state.notes,
        glossUncertain:    state.is_uncertain,
        glossRejected:     state.is_rejected,
        glossKeywords:     state._keywords,
        glossGroupId:      state.gloss_group_id,
        contributionId:    state.contribution_id,
        languages:         state.languages,
        groups:            state.groups,
        loading:           state.loading,
    };
};


export default connect(mapStateToProps)(EDGlossForm);
