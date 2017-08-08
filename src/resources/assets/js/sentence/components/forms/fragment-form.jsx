import React from 'react';
import classNames from 'classnames';
import axios from 'axios';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { requestSuggestions, setFragments, setFragmentData, setTengwar } from '../../actions/admin';
import EDConfig from 'ed-config';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';
import { 
    TYPE_CODE_NEWLINE,
    TYPE_CODE_EXCLUDE,
    TYPE_CODE_INTERPUNCTUATION,
    TYPE_CODE_OPEN_PARANTHESIS,
    TYPE_CODE_CLOSE_PARANTHESIS,
    TYPE_CODE_WORD_CONNEXION
 } from '../../config';
import { transcribe } from '../../../_shared/tengwar';
import EDSpeechSelect from '../../../_shared/components/speech-select';
import EDInflectionSelect from '../../../_shared/components/inflection-select';
import EDTranslationSelect from '../../../_shared/components/translation-select';
import EDTengwarInput from '../../../_shared/components/tengwar-input';

class EDFragmentForm extends EDStatefulFormComponent {
    constructor(props) {
        super(props);

        // Reconstruct the phrase from the latin sentence. Reconstruction adheres to the format
        // defined by the SentenceBuilder class.
        let phrase = '';
        if (Array.isArray(props.latin)) {
            const parts = [];

            for (let i = 0; i < props.latin.length; i += 1) {
                if (i > 0) {
                    parts.push('\n');
                }

                for (let mapping of props.latin[i]) {
                    parts.push(
                        Array.isArray(mapping) ? (
                            mapping.length > 1 ? mapping[1] : props.fragments[mapping[0]].fragment
                        ) : mapping
                    );
                }
            }
            
            phrase = parts.join('');
        }

        this.state = {
            phrase,
            editIndex: -1,
            editIsExcluded: false,
            erroneousIndexes: []
        };
    }

    createFragment(fragment, type, doTranscribe) {
        let tengwar = undefined;

        if (doTranscribe) {
            // retrieve the mode associated with the language currently selected. 
            const language = EDConfig.findLanguage(this.props.language_id);
            let mode = language.tengwar_mode;

            // Transcribe interpunctuations automatically. The _quenya_ setting
            // is used by default for interpunuctation characters, as they are
            // almost identical across languages.
            if (type && ! mode) {
                mode = 'quenya';
            }

            if (mode) {
                tengwar = transcribe(fragment, mode);
            }
        }

        return {
            fragment,
            type,
            tengwar
        };
    }

    editFragment(fragmentIndex, additionalParams = {}) {

        // Make sure that the fragment index lies within the bounds of the array.
        if (fragmentIndex < -1 || fragmentIndex >= this.props.fragments.length) {
            fragmentIndex = -1;
        }
        
        // Some of the tasks necessary to perform prior to editing might be asynchronous, so establish a
        // promise in the event that such an task will be necessary.
        let promise = Promise.resolve(undefined);

        // All fragments are included by default, which means that they must be associated with a gloss
        // and grammar information.
        var exclude = false;

        if (fragmentIndex > -1) {
            // retrieve the fragment and check whether it is exempt from gloss association and grammar information.
            const data = this.props.fragments[fragmentIndex];
            exclude = data.type === TYPE_CODE_EXCLUDE;
            
            // In the event that the fragment is already associated with a gloss, retrieve it.
            if (data.translation_id) {
                promise = axios.get(EDConfig.api(`book/translate/${data.translation_id}`))
                    .then(resp => { 
                        if (!resp.data.sections || !resp.data.sections.length ||
                            !resp.data.sections[0].glosses || resp.data.sections[0].glosses.length < 1) {
                            return undefined;
                        }

                        return resp.data.sections[0].glosses[0];
                    });
            }

            // Set up the form accordingly. The input elements are deliberately -not- synchronised
            // with the component's state, as the changes shouldn't be performed until the client
            // confirms them.
            promise.then(translation => {
                if (! exclude) {
                    this.translationInput.setValue(translation);
                    this.speechInput.setValue(data.speech_id);
                    this.inflectionInput.setValue(data.inflections ? data.inflections : []);
                    this.commentsInput.setValue(data.comments || '');
                }

                this.tengwarInput.setValue(data.tengwar);
                this.tengwarInput.setSubject(data.fragment);

            }).then(() => {
                // Select the first component with an invalid value
                if (! exclude && ! this.translationInput.getValue()) {
                    this.translationInput.focus();
                }
                else if (! this.tengwarInput.getValue()) {
                    this.tengwarInput.focus();
                }
                else if (! exclude && ! this.speechInput.getValue()) {
                    this.speechInput.focus();
                } 
                else if (! exclude && this.inflectionInput.getValue().length < 1) {
                    this.inflectionInput.focus();
                }
            });
        }

        this.setState({
            ...additionalParams,
            editIndex: fragmentIndex,
            editIsExcluded: exclude
        });

        return promise;
    }

    scrollToForm() {
        // add a little delay because it's actually useful in this situation
        window.setTimeout(() => {
            document.querySelector('.fragment-admin-form').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 250);
    }

    submit() {
        // validate all fragments
        const fragments = this.props.fragments;
        axios.post('/admin/sentence/validate-fragment', { fragments })
            .then(this.onFragmentsValid.bind(this), this.onFragmentsInvalid.bind(this));
    }

    onPreviousClick(ev) {
        ev.preventDefault();
        this.props.history.goBack();
    }

    onPhraseChange(ev) {
        ev.preventDefault();

        const currentFragments = this.props.fragments || [];
        const newFragments = [];

        // Split the phrase into fragments
        {   
            const phrase = this.state.phrase
                .replace(/\r\n/g, "\n");

            let buffer = '';
            let flush = false;
            let additionalFragment = undefined;

            const newlines          = "\n";
            const interpunctuations = ',.!?;';
            const connections       = '-·';
            const openParanthesis   = '([';
            const closeParanthesis  = ')]';

            for (let c of phrase) {

                // space?
                if (c === ' ') {
                    flush = true;
                }

                // is it an interpunctuation character?
                else if (interpunctuations.indexOf(c) > -1) {
                    additionalFragment = this.createFragment(c, TYPE_CODE_INTERPUNCTUATION, true);
                    flush = true;
                }

                // ... a new line?
                else if (newlines.indexOf(c) > -1) {
                    additionalFragment = this.createFragment(c, TYPE_CODE_NEWLINE);
                    flush = true;
                }

                // ... or a word connexion?
                else if (connections.indexOf(c) > -1) {
                    additionalFragment = this.createFragment(c, TYPE_CODE_WORD_CONNEXION);
                    flush = true;
                }

                // ... or open paranthesis?
                else if (openParanthesis.indexOf(c) > -1) {
                    additionalFragment = this.createFragment(c, TYPE_CODE_OPEN_PARANTHESIS, true);
                    flush = true;
                }

                // ... or close paranthesis?
                else if (closeParanthesis.indexOf(c) > -1) {
                    additionalFragment = this.createFragment(c, TYPE_CODE_CLOSE_PARANTHESIS, true);
                    flush = true;
                }

                // add regular characters to buffer
                else {
                    buffer += c;
                }

                if (flush) {
                    if (buffer.length > 0) {
                        newFragments.push(this.createFragment(buffer, 0, true));
                        buffer = '';
                    }

                    if (additionalFragment) {
                        newFragments.push(additionalFragment);
                        additionalFragment = undefined;
                    }

                    flush = false;
                }
            }

            if (buffer.length > 0) {
                newFragments.push(this.createFragment(buffer, 0, true));
            }
        }
        
        for (let i = 0; i < newFragments.length; i += 1) {
            const data = newFragments[i];
            const lowerFragment = data.fragment.toLocaleLowerCase();

            // find existing fragments among the fragments already present in the collection
            const existingFragment = 
                (this.props.fragments && this.props.fragments.length > i && this.props.fragments[i].fragment.toLowerCase() === lowerFragment
                    ? this.props.fragments[i] : undefined) || currentFragments.find(f => f.fragment.toLocaleLowerCase() === lowerFragment) || 
                    undefined;

            if (existingFragment !== undefined) {
                // overwrite the fragment with the existing fragment, as it might contain more data
                newFragments[i] = { 
                    ...existingFragment, 
                    fragment: data.fragment,
                    type: data.type
                }; 
            }
        }

        // We can't be editing a fragment.
        this.editFragment(-1, {
            errors: undefined,
            erroneousIndexes: []
        });

        // Make the fragments permanent (in the client) by dispatching the fragments to the Redux component.
        this.props.dispatch(setFragments(newFragments));
    }

    onFragmentClick(data, resolved) {
        const fragmentIndex = this.props.fragments.indexOf(data);
        this.editFragment(fragmentIndex, undefined);
    }

    onFragmentSaveClick(ev) {
        ev.preventDefault();

        const fragment = this.props.fragments[this.state.editIndex];
        
        // Excluded fragments cannot be examined and therefore does not require all of the information
        // otherwise necessary.
        const excluded = this.state.editIsExcluded;
        const refresh = excluded !== (fragment.type === TYPE_CODE_EXCLUDE);

        const translation = excluded ? null : this.translationInput.getValue();
        const inflections = excluded ? []   : this.inflectionInput.getValue() || [];
        const speech_id = excluded   ? null : this.speechInput.getValue();
        const speech = excluded      ? null : this.speechInput.getText();
        const comments = excluded    ? ''   : this.commentsInput.getValue();
        const type = excluded        ? TYPE_CODE_EXCLUDE : 0;
        const tengwar = this.tengwarInput.getValue();

        let fragmentData = {
            speech_id,
            speech,
            inflections,
            comments,
            tengwar,
            type,
            translation_id: translation ? translation.id : undefined
        };

        // If the 'apply to similar words' checkbox is checked, make an array
        // with the indexes of all fragments similar to the one currently being
        // edited. By using the reduce function, the fragments array is reduced
        // to an array with indexes. It works like a filter and adapter at the 
        // same time.
        let indexes = this.applyToSimilarCheckbox.checked 
            ? this.props.fragments.reduce((accumulator, f, i) => {
                // Only modify identical fragments that does _not_ have a translation already associated with it
                if (f.fragment.toLocaleLowerCase() !== fragment.fragment.toLocaleLowerCase() || f.translation_id) {
                    return accumulator; // the fragments are dissimilar.
                }

                return [...accumulator, i]; // fragments are similar = add the index
            }, [])  
            // If the checkbox isn't checked, just update the fragment currently being edited.
            : [ this.state.editIndex ]; 

        this.props.dispatch(setFragmentData(indexes, fragmentData));
        if (refresh) {
            this.props.dispatch(setFragments());
        }

        if (this.state.erroneousIndexes.length === 0) {
            // go to the next fragment in the collection, but skip over interpunuctations.
            let nextIndex = this.state.editIndex + 1;
            while (nextIndex < this.props.fragments.length) {
                fragmentData = this.props.fragments[nextIndex];

                if (! fragmentData.type) {
                    break;
                }

                // interpuncutations -- skip
                nextIndex += 1;
            } 

            if (nextIndex < this.props.fragments.length) {
                // the next index lies within the bounds of the array. Execute in a new
                // thread to leave the event handler.
                window.setTimeout(() => {
                    this.editFragment(nextIndex);
                    this.scrollToForm(); // for mobile devices
                }, 0);

            } else {
                // if the next index is outside the bounds of the array ...
                this.editFragment(-1); // ... consider editing done - close the dialogue!
            }
        } else {
            // submit the form continously when there are erroneous indexes, 
            // as it suggests that the client has been trying to subbmit the form
            // previously but got denied because of a server-side validation error.
            // 
            // By submitting the form, the server side will re-evaluate the content
            // with the new data supplied by the client.
            //
            // Execute the submission on a new thread.
            window.setTimeout(() => {
                this.submit();
            }, 0);
        }
    }

    onFragmentsValid(response) {
        this.setState({
            errors: undefined,
            erroneousIndexes: []
        });

        axios.post('/admin/sentence/parse-fragment/tengwar', { fragments: this.props.fragments }).then(response => {
            this.props.dispatch(setTengwar(response.data));
            this.props.history.goForward();
        });
    }

    onFragmentsInvalid(result) {
        if (result.response.status !== EDConfig.apiValidationErrorStatusCode) {
            return ; // unknown error code
        }

        let errors = [];
        let erroneousIndexes = [];
        for (let erroneousElementName in result.response.data) {
            const parts = /^fragments.([0-9]+).([a-zA-Z0-9_]+)/.exec(erroneousElementName);
            if (parts === null) {
                errors = [...errors, ...result.response.data[erroneousElementName]];
                continue;
            }

            const index = parseInt(parts[1], 10);
            const missing = parts[2];

            if (index < 0 || index >= this.props.fragments.length) {
                continue; // mismatch server/client, probably due to lagging synchronization
            }

            if (erroneousIndexes.indexOf(index) === -1) {
                erroneousIndexes.push(index);
            }

            const fragmentData = this.props.fragments[index];
            errors.push(`${fragmentData.fragment} (${index + 1}-th word) is missing or has an invalid ${missing}.`);
        }

        if (errors.length > 0) {
            this.setState({
                errors,
                erroneousIndexes
            });

            this.editFragment(erroneousIndexes[0]);
            this.scrollToForm();
        }
    }

    onTranslationSelected(ev) {
        if (! ev.value) {
            return;
        }

        const type = ev.value.type;
        if (! type || this.speechInput.getValue()) {
            return;
        }

        const speechId = this.speechInput.getValueForText(type);
        if (! speechId) {
            return;
        }

        this.speechInput.setValue(speechId);
    }

    onFragmentCancel(ev) {
        ev.preventDefault();
        this.editFragment(-1);
    }

    onSubmit(ev) {
        ev.preventDefault();
        if (this.state.erroneousIndexes.length === 0) {
            this.submit();
        }
    }
 
    render() {
        const language = EDConfig.findLanguage(this.props.language_id);

        return <form onSubmit={this.onSubmit.bind(this)}>
            <p>
                This is the second step of a total of three steps. Here you will write down your phrase
                and attach grammatical meaning and analysis to words of your choosing.

                Please try to be as thorough as possible as it will make the database more useful for everyone.
            </p>
            <EDErrorList errors={this.state.errors} />
            <div className="form-group">
                <label htmlFor="ed-sentence-phrase" className="control-label">Phrase</label>
                <textarea id="ed-sentence-phrase" className="form-control" name="phrase" rows="8" 
                    value={this.state.phrase} onChange={this.onChange.bind(this)}></textarea>
            </div>
            <div className="text-right">
                <button className="btn btn-primary" onClick={this.onPhraseChange.bind(this)}><span className="glyphicon glyphicon-refresh" /> Update phrase</button>
            </div>
            <p>
                <strong>Word definitions</strong>
            </p>
            <p>
                Green words are linked to words in the dictionary, whereas red words are not. Please link all
                words before proceeding to the next step.
            </p>
            {this.props.latin.map((line, lineIndex) => <p key={`p${lineIndex}`}>
                {line.map((mapping, fragmentIndex) => {
                return <EDEditableFragment key={`pf${fragmentIndex}`} 
                                           fragments={this.props.fragments} 
                                           mapping={mapping}
                                           index={fragmentIndex}
                                           selected={Array.isArray(mapping) && mapping[0] === this.state.editIndex}
                                           erroneous={this.state.erroneousIndexes.indexOf(fragmentIndex) > -1}
                                           onClick={this.onFragmentClick.bind(this)} />
                })}
            </p>)}
            <div className="fragment-admin-form">
                {this.state.editIndex > -1 ?
                (this.props.loading ? (
                    <div>
                        <div className="sk-spinner sk-spinner-pulse"></div>
                        <p className="text-center"><em>Loading ...</em></p>
                    </div> 
                ) : (
                    <div className="well">
                        {! this.state.editIsExcluded ? <div className="form-group">
                            <label htmlFor="ed-sentence-fragment-word" className="control-label">Word (<em>{this.props.fragments[this.state.editIndex].fragment}</em> uninflected)</label>
                            <EDTranslationSelect componentId="ed-sentence-fragment-word" 
                                languageId={this.props.language_id}
                                required={true}
                                ref={input => this.translationInput = input}
                                onChange={this.onTranslationSelected.bind(this)} />
                        </div> : ''}
                        <div className="form-group">
                            <label htmlFor="ed-sentence-fragment-tengwar" className="control-label">Tengwar</label>
                            <EDTengwarInput componentId="ed-sentence-fragment-tengwar" tengwarMode={language.tengwar_mode}
                                ref={input => this.tengwarInput = input} />
                        </div>
                        {! this.state.editIsExcluded ? <div>
                            <div className="form-group">
                                <label htmlFor="ed-sentence-fragment-speech" className="control-label">Type of speech</label>
                                <EDSpeechSelect componentId="ed-sentence-fragment-speech" 
                                    ref={input => this.speechInput = input} />
                            </div>
                            <div className="form-group">
                                <label htmlFor="ed-sentence-fragment-inflections" className="control-label">Inflection(s)</label>
                                <EDInflectionSelect componentId="ed-sentence-fragment-inflections" 
                                    ref={input => this.inflectionInput = input} />
                            </div>
                            <div className="form-group">
                                <label htmlFor="ed-sentence-fragment-comments" className="control-label">Comments</label>
                                <EDMarkdownEditor componentId="ed-sentence-fragment-comments" rows={4} 
                                    ref={input => this.commentsInput = input} />
                            </div>
                        </div> : ''}
                        <div className="form-group">
                            <div className="checkbox">
                                <label>
                                    <input type="checkbox" name="editIsExcluded" checked={this.state.editIsExcluded} 
                                        onChange={super.onChange.bind(this)} />
                                    {' Disable examination (useful for names and words that cannot be translated)'}
                                </label>
                            </div>
                        </div>
                        <div className="form-group">
                            <div className="checkbox">
                                <label>
                                    <input type="checkbox" ref={input => this.applyToSimilarCheckbox = input} />
                                    {' Apply changes to similar words'}
                                </label>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="btn-group">
                                <button className="btn btn-default" onClick={this.onFragmentCancel.bind(this)}>Cancel</button>
                                <button className="btn btn-primary" onClick={this.onFragmentSaveClick.bind(this)}>Save and go forward</button>
                            </div>
                        </div>
                    </div>
                )) : ''}
            </div>
            <nav>
                <ul className="pager">
                    <li className="previous"><a href="#" onClick={this.onPreviousClick.bind(this)}>&larr; Previous step</a></li>
                    <li className={classNames('next', { 'disabled': this.state.erroneousIndexes.length > 0 })}>
                        <a href="#" onClick={this.onSubmit.bind(this)}>Next step &rarr;</a>
                    </li>
                </ul>
            </nav>
        </form>;
    }
}

class EDEditableFragment extends React.Component {
    onFragmentClick(ev) {
        ev.preventDefault();
        
        if (this.props.onClick) {
            let resolved = false;
            let mapping = this.props.mapping; 
            
            if (! Array.isArray(mapping)) {
                resolved = true;
                mapping = this.tryResolveMapping();
            }

            if (mapping) {
                window.setTimeout(() => this.props.onClick(this.props.fragments[mapping[0]], resolved), 0);
            }
        }
    }

    tryResolveMapping() {
        // Admin feature: double check for excluded fragments. These fragments will be rendered as 
        // text elements and thus won't be clickable. By looking for the fragment among all fragments, 
        // matching by string comparison, excluded fragments can be found, and a mapping restored.
        const index = this.props.fragments.findIndex(
            (f, i) => f.fragment === this.props.mapping && 
                        f.type === TYPE_CODE_EXCLUDE && 
                        i <= this.props.index
        );

        return index === -1 ? undefined : [index];
    }

    render() {
        let mapping = this.props.mapping;
        let text = undefined;

        const selected = this.props.selected;
        const erroneous = this.props.erroneous;

        // string mapping?
        if (! Array.isArray(mapping)) {
            text = mapping;

            // Try to resolve a mapping, in the event that the string is in fact an excluded fragment.
            mapping = this.tryResolveMapping();
            if (! mapping) {
                return <span>{text}</span>;
            }
        }

        // retrieve the fragment, and determine whether its fragment is overwritten (by a 2nd element in the array)  
        const fragment = this.props.fragments[mapping[0]];
        text = undefined;
        if (mapping.length > 1) {
            text = mapping[1];
        }

        if (fragment.type && fragment.type !== TYPE_CODE_EXCLUDE) {
            return <span>{text || fragment.fragment}</span>;
        }

        return <a href="#" onClick={this.onFragmentClick.bind(this)}
            className={classNames('label', 'ed-sentence-fragment', { 
                'label-success': (fragment.type === TYPE_CODE_EXCLUDE || !! fragment.translation_id) && ! selected && ! erroneous, 
                'label-warning': erroneous,
                'label-danger': ! fragment.translation_id && fragment.type !== TYPE_CODE_EXCLUDE && ! selected,
                'label-primary': selected
            })}>
                {selected 
                    ? <span><span className="glyphicon glyphicon-pencil"></span>&#32;</span> 
                    : (erroneous ? <span><span className="glyphicon glyphicon-warning-sign"></span>&#32;</span> : '')}
                {text || fragment.fragment}
            </a>;
    }
}

EDEditableFragment.defaultProps = {
    selected: false,
    erroneous: false,
    fragments: {},
    mapping: [],
    index: -1
};

const mapStateToProps = state => {
    return {
        languages: state.languages,
        language_id: state.language_id,
        fragments: state.fragments,
        latin: state.latin,
        loading: state.loading
    };
};

export default withRouter(connect(mapStateToProps)(EDFragmentForm));
