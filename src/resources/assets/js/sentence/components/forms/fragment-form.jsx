import React from 'react';
import classNames from 'classnames';
import axios from 'axios';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { requestSuggestions, setFragments, setFragmentData } from '../../actions/admin';
import EDConfig from 'ed-config';
import { EDStatefulFormComponent } from 'ed-form';
import { transcribe } from '../../../_shared/tengwar';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';
import EDSpeechSelect from '../../../_shared/components/speech-select';
import EDInflectionSelect from '../../../_shared/components/inflection-select';
import EDTranslationSelect from '../../../_shared/components/translation-select';

class EDFragmentForm extends EDStatefulFormComponent {
    constructor(props) {
        super(props);

        // Reconstruct the phrase from the sentence fragments. Only one rule needs to 
        // be observed: add a space in front of the fragment, unless it contains a
        // interpunctuation character.
        let phrase = '';
        if (Array.isArray(props.fragments)) {
            phrase = props.fragments.map(
                (f, i) => (i === 0 || f.interpunctuation ? '' : ' ') + f.fragment)
                .join('');
        }

        this.state = {
            phrase,
            editingFragmentIndex: -1,
            erroneousIndexes: []
        };
    }

    createFragment(fragment, interpunctuation) {
        let tengwar = undefined;

        // Transcribe interpunctuations automatically. The _quenya_ setting
        // is used for all interpunctuations as they are essentially the same
        // across languages.
        const is_linebreak = /^\n$/.test(fragment);
        if (interpunctuation && ! is_linebreak) {
            tengwar = transcribe(fragment, 'quenya');
        }

        return {
            fragment,
            interpunctuation,
            tengwar,
            is_linebreak
        };
    }

    editFragment(fragmentIndex, additionalParams) {
        if (additionalParams === undefined) {
            additionalParams = {};
        }

        if (fragmentIndex < -1 || fragmentIndex >= this.props.fragments.length) {
            fragmentIndex = -1;
        }
        
        let promise = Promise.resolve(undefined);
        if (fragmentIndex > -1) {
            const data = this.props.fragments[fragmentIndex];
            
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

            promise.then(translation => {
                this.translationInput.setValue(translation);
                this.speechInput.setValue(data.speech_id);
                this.inflectionInput.setValue(data.inflections ? data.inflections : []);
                this.tengwarInput.value = data.tengwar || '';
                this.commentsInput.setValue(data.comments || '');
            });
        }

        this.setState({
            ...additionalParams,
            editingFragmentIndex: fragmentIndex
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

            const interpunctuationReg = /^[,\.!\?\n]$/;

            for (let c of phrase) {

                // space?
                if (c === ' ') {
                    flush = true;
                }

                // is it an interpunctuation character or a new line?
                else if (interpunctuationReg.test(c)) {
                    additionalFragment = this.createFragment(c, true);
                    flush = true;
                } 

                // add regular characters to buffer
                else {
                    buffer += c;
                }

                if (flush) {
                    if (buffer.length > 0) {
                        newFragments.push(this.createFragment(buffer, false));
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
                newFragments.push(buffer, false);
            }
        }

        const words = [];
        for (let i = 0; i < newFragments.length; i += 1) {
            const data = newFragments[i];
            const lowerFragment = data.fragment.toLocaleLowerCase();
            const existingFragment = currentFragments.find(f => f.fragment.toLocaleLowerCase() === lowerFragment) || undefined;

            if (existingFragment !== undefined) {
                // overwrite the fragment with the existing fragment, as it might contain more data
                newFragments[i] = { 
                    ...existingFragment, 
                    fragment: data.fragment,
                    is_linebreak: data.is_linebreak
                }; 
            }

            if (!newFragments[i].interpunctuation) {
                words.push(newFragments[i].fragment);
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

    onFragmentClick(data) {
        const fragmentIndex = this.props.fragments.indexOf(data);
        this.editFragment(fragmentIndex);
    }

    onTranscribeClick(ev) {
        ev.preventDefault();

        const language = this.props.languages.find(l => l.id === this.props.language_id);
        const data = this.props.fragments[this.state.editingFragmentIndex];

        let transcription = null;
        if (language.tengwar_mode) {
            transcription = transcribe(data.fragment, language.tengwar_mode, false);
        }

        if (transcription) {
            this.tengwarInput.value = transcription;
        } else {
            errors = [`Unfortunately, the transcription service does not support ${language.name}.`];
            this.setState({
                errors
            });
        }
    }

    onFragmentSaveClick(ev) {
        ev.preventDefault();

        const fragment = this.props.fragments[this.state.editingFragmentIndex];
        const translation = this.translationInput.getValue();
        const inflections = this.inflectionInput.getValue() || [];
        const speech_id = this.speechInput.getValue();
        const comments = this.commentsInput.getValue();
        const tengwar = this.tengwarInput.value;

        let fragmentData = {
            speech_id,
            inflections,
            comments,
            tengwar,
            translation_id: translation ? translation.id : undefined,
            is_linebreak: fragment.is_linebreak
        };

        // If the 'apply to similar words' checkbox is checked, make an array
        // with the indexes of all fragments similar to the one currently being
        // edited. By using the reduce function, the fragments array is reduced
        // to an array with indexes. It works like a filter and adapter at the 
        // same time.
        let indexes = this.applyToSimilarCheckbox.checked 
            ? this.props.fragments.reduce((accumulator, f, i) => {
                if (f.fragment !== fragment.fragment) {
                    return accumulator; // the fragments are dissimilar.
                }

                return [...accumulator, i]; // fragments are similar = add the index
            }, [])  
            // If the checkbox isn't checked, just update the fragment currently being edited.
            : [ this.state.editingFragmentIndex ]; 

        this.props.dispatch(setFragmentData(indexes, fragmentData));

        if (this.state.erroneousIndexes.length === 0) {
            // go to the next fragment in the collection, but skip over interpunuctations.
            let nextIndex = this.state.editingFragmentIndex + 1;
            while (nextIndex < this.props.fragments.length) {
                fragmentData = this.props.fragments[nextIndex];

                if (!fragmentData.interpunctuation) {
                    break;
                }

                // interpuncutations -- skip
                nextIndex += 1;
            } 

            if (nextIndex < this.props.fragments.length) {
                // the next index lies within the bounds of the array. Execute in a new
                // thread to leave the event handler.
                window.setTimeout(() => {
                    this.editFragment(nextIndex).then(() => {
                        this.translationInput.focus();
                    });
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

        this.props.history.goForward();
    }

    onFragmentsInvalid(result) {
        if (result.response.status !== EDConfig.apiValidationErrorStatusCode) {
            return ; // unknown error code
        }

        let errors = [];
        let erroneousIndexes = [];
        for (let erroneousElementName in result.response.data) {
            const parts = /^fragments.([0-9]+).([a-zA-Z0-9_]+)/.exec(erroneousElementName);
            if (parts.length < 3) {
                continue; // unsupported response format
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

        if (erroneousIndexes.length > 0) {
            this.setState({
                errors,
                erroneousIndexes
            });

            this.editFragment(erroneousIndexes[0]);
            this.scrollToForm();
        }
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
        return <form onSubmit={this.onSubmit.bind(this)}>
            <p>
                This is the second step of a total of three steps. Here you will write down your phrase
                and attach grammatical meaning and analysis to words of your choosing.

                Please try to be as thorough as possible as it will make the database more useful for everyone.
            </p>
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
            <p>
                {this.props.fragments.map((f, i) => <EDFragment key={i} 
                    fragment={f} 
                    selected={i === this.state.editingFragmentIndex}
                    erroneous={this.state.erroneousIndexes.indexOf(i) > -1}
                    onClick={this.onFragmentClick.bind(this)} />)}
            </p>
            <div className="fragment-admin-form">
                {this.state.editingFragmentIndex > -1 ?
                (this.props.loading ? (
                    <div>
                        <div className="sk-spinner sk-spinner-pulse"></div>
                        <p className="text-center"><em>Loading ...</em></p>
                    </div> 
                ) : (
                    <div className="well">
                        <EDErrorList errors={this.state.errors} />
                        <div className="form-group">
                            <label htmlFor="ed-sentence-fragment-word" className="control-label">Word</label>
                            <EDTranslationSelect componentId="ed-sentence-fragment-word" languageId={this.props.language_id}
                                suggestions={this.props.suggestions 
                                    ? this.props.suggestions[this.props.fragments[this.state.editingFragmentIndex].fragment]
                                    : []}
                                ref={input => this.translationInput = input} />
                        </div>
                        <div className="form-group">
                            <label htmlFor="ed-sentence-fragment-tengwar" className="control-label">Tengwar</label>
                            <div className="input-group">
                                <input id="ed-sentence-fragment-tengwar" className="form-control tengwar" type="text" 
                                    ref={input => this.tengwarInput = input} />
                                <div className="input-group-addon">
                                    <a href="#" onClick={this.onTranscribeClick.bind(this)}>Transcribe</a>
                                </div>
                            </div>
                        </div>
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
                        <div className="form-group">
                            <div className="checkbox">
                                <label>
                                    <input type="checkbox" ref={input => this.applyToSimilarCheckbox = input} />
                                    {' Apply changes to similar words.'}
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

class EDFragment extends React.Component {
    onFragmentClick(ev) {
        ev.preventDefault();
        
        if (this.props.onClick) {
            window.setTimeout(() => this.props.onClick(this.props.fragment), 0);
        }
    }

    render() {
        const data = this.props.fragment;
        const selected = this.props.selected;
        const erroneous = this.props.erroneous;

        if (data.interpunctuation) {
            if (/^[\n]+$/.test(data.fragment)) {
                return <br />;
            }

            return <span>{data.fragment}</span>;
        }

        return <span>{' '}<a href="#" onClick={this.onFragmentClick.bind(this)}
            className={classNames('label', 'ed-sentence-fragment', { 
                'label-success': !! data.translation_id && !selected && !erroneous, 
                'label-warning': erroneous,
                'label-danger': ! data.translation_id && !selected,
                'label-primary': selected
            })}>
                {selected 
                    ? <span><span className="glyphicon glyphicon-pencil"></span>&#32;</span> 
                    : (erroneous ? <span><span className="glyphicon glyphicon-warning-sign"></span>&#32;</span> : '')}
                {data.fragment}
            </a>
        </span>;
    }
}

EDFragment.defaultProps = {
    selected: false,
    erroneous: false,
    fragment: {}
};

const mapStateToProps = state => {
    return {
        languages: state.languages,
        language_id: state.language_id,
        fragments: state.fragments,
        suggestions: state.suggestions,
        loading: state.loading
    };
};

export default withRouter(connect(mapStateToProps)(EDFragmentForm));
