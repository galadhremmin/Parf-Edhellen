import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
import axios from 'axios';
import { polyfill as enableSmoothScrolling } from 'smoothscroll-polyfill';
import { EDStatefulFormComponent } from '../../../_shared/form';
import EDMarkdownEditor from '../../../_shared/components/markdown-editor';
import EDErrorList from '../../../_shared/components/error-list';

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
            phrase: phrase,
            fragments: props.fragments || []
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

        const currentFragments = this.state.fragments || [];
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
                const insertAt = i === 0 ? i : i + 1;
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
                newFragments[i] = existingFragment; 
            }

            if (!newFragments[i].interpunctuation) {
                words.push(newFragments[i].fragment);
            }
        }

        this.setState({
            fragments: newFragments
        });

        if (words.length > 0) {
            axios.post(window.EDConfig.api('/book/suggest'), { 
                words, 
                language_id: this.props.languageId 
            });
        }
    }

    onFragmentClick(data) {
        
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
                {this.state.fragments.map((f, i) => <EDFragment key={i} fragment={f} onClick={this.onFragmentClick.bind(this)} />)}
            </p>
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

        if (data.interpunctuation) {
            if (/^[\n]+$/.test(data.fragment)) {
                return <br />;
            }

            return <span>{data.fragment}</span>;
        }

        return <span>{' '}<a href="#" onClick={this.onFragmentClick.bind(this)}>{data.fragment}</a></span>;
    }
}

const mapStateToProps = state => {
    return {
        languages: state.languages,
        languageId: state.language_id,
        fragments: state.fragments
    };
};

export default withRouter(connect(mapStateToProps)(EDFragmentForm));
