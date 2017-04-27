import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { transcribe as transcribeGeneralUse, makeOptions as makeOptionsForGeneralUse } from 'tengwar/general-use';
import { transcribe as transcribeClassical, makeOptions as makeOptionsForClassical } from 'tengwar/classical';
import TengwarParmaite from 'tengwar/tengwar-parmaite';
import { requestSuggestions, setFragments, setFragmentData } from '../../actions/admin';
import { EDStatefulFormComponent } from 'ed-form';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import EDErrorList from 'ed-components/error-list';

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
                    newFragments.splice(i, 1);

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

        if (words.length > 0) {
            // Request suggestions from the server.
            this.props.dispatch(requestSuggestions(words, this.props.language_id));
        }
    }

    onFragmentClick(data) {
        this.setState({
            editingFragmentIndex: this.props.fragments.indexOf(data)
        });

        this.tengwarInput.value = data.tengwar || '';
        this.commentsInput.setValue(data.comments || '');
    }

    onTranscribeClick(ev) {
        ev.preventDefault();

        const language = this.props.languages.find(l => l.id === this.props.language_id);

        let options = null;
        let transcriber = null;

        switch (language.tengwar_mode) {
            case 'general-use':
                options = makeOptionsForGeneralUse({
                    font: TengwarParmaite
                });
                transcriber = transcribeGeneralUse;
                break;
            case 'classical':
                options = makeOptionsForClassical({
                    font: TengwarParmaite
                });
                transcriber = transcribeClassical;
                break;
            default:
                // unsupported!
                return;
        }

        const data = this.props.fragments[this.state.editingFragmentIndex];
        const transcription = transcriber(data.fragment, options);

        if (transcription) {
            this.tengwarInput.value = transcription;
        }
    }

    onSubmit() {
        
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
            {this.props.loading ? 
                <div>
                    <div className="sk-spinner sk-spinner-pulse"></div>
                    <p className="text-center"><em>Loading suggestions ...</em></p>
                </div>
            : 
                <p>
                    {this.props.fragments.map((f, i) => <EDFragment key={i} 
                        fragment={f} 
                        selected={i === this.state.editingFragmentIndex}
                        onClick={this.onFragmentClick.bind(this)} />)}
                </p>
            }
            {this.state.editingFragmentIndex > -1 ? 
                <div className="well">
                    <div className="form-group">
                        <label htmlFor="ed-sentence-fragment-tengwar" className="control-label">Tengwar</label>
                        <div className="input-group">
                            <input id="ed-sentence-fragment-tengwar" className="form-control tengwar" type="text" 
                                ref={input => this.tengwarInput = input} />
                            <div className="input-group-addon"><a href="#" onClick={this.onTranscribeClick.bind(this)}>Automatic transcription</a></div>
                        </div>
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-sentence-fragment-comments" className="control-label">Comments</label>
                        <EDMarkdownEditor componentId="ed-sentence-fragment-comments" ref={input => this.commentsInput = input} rows={4} />
                    </div>
                </div>
            : ''}
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
        loading: state.loading
    };
};

export default withRouter(connect(mapStateToProps)(EDFragmentForm));
