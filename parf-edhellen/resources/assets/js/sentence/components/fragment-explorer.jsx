import React from 'react';
import { connect } from 'react-redux';
import { selectFragment } from '../actions';
import EDFragment from './fragment';
import EDTengwarFragment from './tengwar-fragment';
import EDBookGloss from '../../search/components/book-gloss';
import { Parser as HtmlToReactParser } from 'html-to-react';

class EDFragmentExplorer extends React.Component {
    constructor(props) {
        super(props);

    }

    componentDidMount() {

    }

    onFragmentClick(ev) {
        this.props.dispatch(selectFragment(ev.id, ev.translationId));
    }

    render() {
        let section = null;
        let fragment = null;
        let parser = null;

        if (!this.props.loading && this.props.bookData && this.props.bookData.sections.length > 0) {
            section = this.props.bookData.sections[0];
            fragment = this.props.fragments.find(f => f.id === this.props.fragmentId);
            parser = new HtmlToReactParser();
        }

        return <div className="well ed-fragment-navigator">
            <p className="tengwar ed-tengwar-fragments">
                { this.props.fragments.map(f => <EDTengwarFragment fragment={f}
                                                                   key={`tng${f.id}`}
                                                                   selected={f.id === this.props.fragmentId} />) }
            </p>
            <p className="ed-elvish-fragments">
                { this.props.fragments.map(f => <EDFragment fragment={f}
                                                            key={`frg${f.id}`}
                                                            selected={f.id === this.props.fragmentId}
                                                            onClick={this.onFragmentClick.bind(this)} />) }
            </p>
            {this.props.loading
                ? <div className="sk-spinner sk-spinner-pulse"></div>
                : (section ? (<div>
                {fragment.grammarType ? <div><em>{fragment.grammarType}</em></div> : ''}
                <div>{fragment.comments ? parser.parse(fragment.comments) : ''}</div>
                <hr />
                <div>
                    {section.glosses.map(g => <EDBookGloss gloss={g}
                                                           language={section.language}
                                                           key={g.TranslationID} />)}
                </div>
            </div>) : '')}
        </div>;
    }
}

const mapStateToProps = (state) => {
    return {
        fragments: state.fragments,
        fragmentId: state.fragmentId,
        bookData: state.bookData,
        loading: state.loading
    };
};

export default connect(mapStateToProps)(EDFragmentExplorer);
