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
            editingFragmentIndex: -1
        };
    }

    createFragment(fragment, interpunctuation) {
        return {
            fragment,
            interpunctuation
        };
    }

    onPreviousClick(ev) {
        ev.preventDefault();
        this.props.history.goBack();
    }

    onPhraseChange(ev) {
        ev.preventDefault();

        const currentFragments = this.props.fragments || [];
        const newFragments = this.state.phrase
            .replace(/\r\n/g, "\n")
            .split(' ')
            .map(f => this.createFragment(f));

        for (let i = 0; i < newFragments.length; i += 1) {
            const data = newFragments[i];
            if (data.interpunctuation) {
                continue;
            }

            // Find interpunctuation and new line fragments, and remove them from the actual
            // word fragment. These should be registered as fragments of their own.
            for (let fi = 0; fi < data.fragment.length; fi += 1) {
                if (!/^[,\.!\?\s]$/.test(data.fragment[fi])) {
                    continue;
                }

                // Should the fragment be inserted in front of the current fragment or after it?
                // This is determined by looking at the cursor's position (_fi_). If it is at
                // in its initial position (= 0) then the interpunctutation fragment should be
                // placed in front of it, otherwise after. 
                const insertAt = fi === 0 ? i : i + 1;
                newFragments.splice(insertAt, 0, this.createFragment(data.fragment[fi], true));

                // are there more of the fragment after the interpunctuation?
                if (fi + 1 < data.fragment.length) {
                    newFragments.splice(insertAt + 1, 0, this.createFragment(data.fragment.substr(fi + 1)));
                } 
                
                if (fi > 0) {
                    data.fragment = data.fragment.substr(0, fi);

                    i -= 1;
                } else {
                    newFragments.splice(insertAt + 1, 1);

                    i -= 2;
                }

                break;
            }
        }

        const words = [];
        for (let i = 0; i < newFragments.length; i += 1) {
            const data = newFragments[i];
            const lowerFragment = data.fragment.toLocaleLowerCase();
            const existingFragment = currentFragments.find(f => f.fragment.toLocaleLowerCase() === lowerFragment) || undefined;

            if (existingFragment !== undefined) {
                // overwrite the fragment with the existing fragment, as it might contain more data
                newFragments[i] = { ...existingFragment, fragment: data.fragment }; 
            }

            if (!newFragments[i].interpunctuation) {
                words.push(newFragments[i].fragment);
            }
        }

        // We can't be editing a fragment.
        this.setState({
            editingFragmentIndex: -1
        });

        // Make the fragments permanent (in the client) by dispatching the fragments to the Redux component.
        this.props.dispatch(setFragments(newFragments));
    }

    onFragmentClick(data) {
        let promise;
        if (data.translation_id) {
            promise = axios.get(EDConfig.api(`book/translate/${data.translation_id}`))
                .then(resp => { 
                    if (!resp.data.sections || !resp.data.sections.length ||
                        !resp.data.sections[0].glosses || resp.data.sections[0].glosses.length < 1) {
                        return undefined;
                    }

                    return resp.data.sections[0].glosses[0];
                });
        } else {
            promise = Promise.resolve(undefined);
        }

        promise.then(translation => {
            this.setState({
                editingFragmentIndex: this.props.fragments.indexOf(data)
            });

            this.translationInput.setValue(translation);
            this.speechInput.setValue(data.speech_id);
            this.inflectionInput.setValue(data.inflections ? data.inflections : []);
            this.tengwarInput.value = data.tengwar || '';
            this.commentsInput.setValue(data.comments || '');
        });
    }

    onTranscribeClick(ev) {
        ev.preventDefault();

        const language = this.props.languages.find(l => l.id === this.props.language_id);
        const data = this.props.fragments[this.state.editingFragmentIndex];

        let transcription = transcribe(data.fragment, language.tengwar_mode, false);
        let errors = undefined;
        if (transcription) {
            this.tengwarInput.value = transcription;
        } else {
            errors = [`Unfortunately, the transcription service does not support ${language.name}.`];
        }

        this.setState({
            errors
        });
    }

    onFragmentSaveClick(ev) {
        ev.preventDefault();

        const fragment = this.props.fragments[this.state.editingFragmentIndex];
        const translation = this.translationInput.getValue();
        const inflections = this.inflectionInput.getValue();
        const speech_id = this.speechInput.getValue();
        const comments = this.commentsInput.getValue();
        const tengwar = this.tengwarInput.value;

        const fragmentData = {
            translation_id: translation ? translation.id : undefined,
            speech_id,
            inflections,
            comments,
            tengwar
        };

        let indexes = this.applyToSimilarCheckbox.checked 
            ? this.props.fragments.reduce((accumulator, f, i) => {
                if (f.fragment !== fragment.fragment) {
                    return accumulator;
                }

                return [...accumulator, i];
            }, []) : [ this.state.editingFragmentIndex ];

        this.props.dispatch(setFragmentData(indexes, fragmentData));

        this.setState({
            editingFragmentIndex: -1
        });
    }

    onSubmit(ev) {
        ev.preventDefault();
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
                    onClick={this.onFragmentClick.bind(this)} />)}
            </p>
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
                            <button className="btn btn-primary" onClick={this.onFragmentSaveClick.bind(this)}>Update</button>
                        </div>
                    </div>
                )) : ''}
            <nav>
                <ul className="pager">
                    <li className="previous"><a href="#" onClick={this.onPreviousClick.bind(this)}>&larr; Previous step</a></li>
                    <li className="next">
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

        if (data.interpunctuation) {
            if (/^[\n]+$/.test(data.fragment)) {
                return <br />;
            }

            return <span>{data.fragment}</span>;
        }

        return <span>{' '}<a href="#" onClick={this.onFragmentClick.bind(this)}
            className={classNames('label', 'ed-sentence-fragment', { 
                'label-success': !! data.translation_id && !selected, 
                'label-danger': ! data.translation_id && !selected,
                'label-primary': selected
            })}>
                {selected ? <span><span className="glyphicon glyphicon-pencil"></span>&#32;</span> : ''}
                {data.fragment}
            </a>
        </span>;
    }
}

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
