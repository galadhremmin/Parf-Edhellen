import React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import { connect } from 'react-redux';
import EDConfig from 'ed-config';
import { selectFragment } from '../actions';
import EDFragment from './fragment';
import EDBookGloss from '../../search/components/book-gloss';
import { Parser as HtmlToReactParser } from 'html-to-react';

class EDFragmentExplorer extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            fragmentIndex: 0,
            detailsAsOverlay: false
        };
    }

    /**
     * Component will mount, which is an opening for event hooks
     */
    componentWillMount() {
        window.addEventListener('scroll', this.onWindowScroll.bind(this));
    }

    /**
     * Component has mounted and initial state retrieved from the server should be applied.
     */
    componentDidMount() {
        let fragmentIndex = this.nextFragmentIndex(-1);
        // Does the shebang specify the fragment ID?
        if (/^#![0-9]+$/.test(window.location.hash)) {
            const fragmentId = parseInt(String(window.location.hash).substr(2), 10);
            if (fragmentId) {
                fragmentIndex = Math.max(this.props.fragments.findIndex(f => f.id === fragmentId), 0);
            }
        }

        // A little hack for causing the first fragment to be highlighted
        this.onNavigate({}, fragmentIndex);
    }

    /**
     * Retrieves the fragment index for the next fragment, or returns the current fragment index
     * if none exists.
     */
    nextFragmentIndex(rootIndex = this.state.fragmentIndex) {
        for (let i = rootIndex + 1; i < this.props.fragments.length; i += 1) {
            const fragment = this.props.fragments[i];

            if (! fragment.type) {
                return i;
            }
        }

        return this.state.fragmentIndex;
    }

    /**
     * Retrieves the fragment index for the previous fragment, or returns the current fragment index
     * if none exists.
     */
    previousFragmentIndex() {
        for (let i = this.state.fragmentIndex - 1; i > -1; i -= 1) {
            const fragment = this.props.fragments[i];

            if (! fragment.type) {
                return i;
            }
        }

        return this.state.fragmentIndex;
    }

    /**
     * Scrolling event triggered by the window.
     * @param {Event} ev 
     */
    onWindowScroll(ev) {
        if (! this.fragmentContainer) {
            return;
        }

        const containerRect = this.fragmentContainer.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        
        const overlay = (viewportHeight - containerRect.bottom < viewportHeight * 0.3) && // Details (not overlay) inferred to require at least 30 % of available height 
            containerRect.top <= viewportHeight * 0.4; // restrict overlay until the fragment is visible, which is relevant on smaller devices
        const change = this.state.detailsAsOverlay !== overlay;

        if (! change) {
            return;
        }

        this.setState({
            detailsAsOverlay: overlay
        });

        // Add padding to the bottom of the document body to accomodate for the height of the overlay
        if (overlay) {
            document.body.classList.add('ed-fragment-details-overlaid');
        } else {
            document.body.classList.remove('ed-fragment-details-overlaid');
        }
    }

    /**
     * Event handler for the onFragmentClick event.
     * 
     * @param {*} ev 
     */
    onFragmentClick(ev) {
        if (ev.preventDefault) { 
            ev.preventDefault();
        }

        const fragmentIndex = this.props.fragments.findIndex(f => f.id === ev.id);
        if (fragmentIndex === -1) {
            return;
        }

        this.setState({
            fragmentIndex
        });
        if (this.props.stateInUrl) {
            window.location.hash = `!${ev.id}`;
        }
    
        this.props.dispatch(selectFragment(ev.id, ev.gloss_id));
    }

    /**
     * Navigates to the specified fragment index by receiving it from the array of fragments, and
     * dispatching a select fragment signal.
     * 
     * @param {*} ev 
     * @param {*} fragmentIndex 
     */
    onNavigate(ev, fragmentIndex) {
        if (ev.preventDefault) {
            ev.preventDefault();
        }
    
        const fragment = this.props.fragments[fragmentIndex];
        this.onFragmentClick({
            id: fragment.id,
            gloss_id: fragment.gloss_id
        })
    }

    /**
     * Dispatches a window message to the search result component, requesting a search.
     * @param {*} data 
     */
    onReferenceLinkClick(data) {
        EDConfig.message(EDConfig.messageNavigateName, data);
    }

    renderFragment(paragraphIndex, mapping, fragmentIndex) {
        let fragment = undefined;
        let text = undefined;

        if (Array.isArray(mapping)) {
            fragment = this.props.fragments[mapping[0]];

            if (mapping.length > 1) {
                text = mapping[1];
            }
        } else {
            text = mapping;
        }

        const selected = fragment && fragment.id === this.props.fragmentId;
        return <EDFragment fragment={fragment}
                           text={text}
                           key={`p${paragraphIndex}.f${fragmentIndex}`}
                           selected={selected}
                           onClick={this.onFragmentClick.bind(this)} />;
    }

    render() {
        let section = null;
        let fragment = null;
        let parser = null;

        if (!this.props.loading && this.props.bookData && this.props.bookData.sections.length > 0) {
            // Comments may contain HTML because it's parsed by the server as markdown. The HtmlToReact parser will
            // examine the HTML and turn it into React components.
            section = this.props.bookData.sections[0];
            fragment = this.props.fragments.find(f => f.id === this.props.fragmentId);
            parser = new HtmlToReactParser();
        }

        const previousIndex = this.previousFragmentIndex();
        const nextIndex = this.nextFragmentIndex();

        return <div className="well ed-fragment-navigator">
            <div className="row" ref={elem => this.fragmentContainer = elem}>
                <div className="col-md-12 col-lg-6">
                {this.props.tengwar.map((paragraph, fi) => 
                    <p className="tengwar ed-tengwar-fragments" key={`p${fi}`}>
                        {paragraph.map(this.renderFragment.bind(this, fi))}
                    </p>
                )}
                </div>
                <hr className="hidden-lg" />
                <div className="col-md-12 col-lg-6">
                {this.props.latin.map((paragraph, fi) => 
                    <p className="ed-elvish-fragments" key={`p${fi}`}>
                        {paragraph.map(this.renderFragment.bind(this, fi))}
                    </p>
                )}
                </div>
            </div>
            <hr className="hidden-xs hidden-sm hidden-md" />
            <div className={classNames('ed-fragment-details', { 'overlay': this.state.detailsAsOverlay })}>
                <small className="text-info">This is a floating overlay. You can scroll within.</small>
                <nav>
                    <ul className="pager">
                        <li className={classNames('previous', { 'hidden': previousIndex === this.state.fragmentIndex })}>
                            <a href="#" onClick={ev => this.onNavigate(ev, previousIndex)}>&larr; {this.props.fragments[previousIndex].fragment}</a>
                        </li>
                        {fragment ? 
                        <li>
                            <strong>{fragment.fragment}</strong>
                        </li> : ''}
                        <li className={classNames('next', { 'hidden': nextIndex === this.state.fragmentIndex })}>
                            <a href="#" onClick={ev => this.onNavigate(ev, nextIndex)}>{this.props.fragments[nextIndex].fragment} &rarr;</a>
                        </li>
                    </ul>
                </nav>
                {this.props.loading
                    ? <div className="sk-spinner sk-spinner-pulse"></div>
                    : (section ? (<div ref={elem => this.fragmentDetails = elem}>
                        <div>{fragment.comments ? parser.parse(fragment.comments) : ''}</div>
                        <div>
                            <span className="label label-success ed-inflection">{fragment.speech}</span>
                            {' '}
                            {fragment.inflections.map((infl, i) => 
                                <span key={`infl${fragment.id}-${i}`}>
                                    <span className="label label-success ed-inflection">{infl.name}</span>
                                    &nbsp;
                                </span>
                            )}
                        </div>
                        <div>
                            {section.glosses.map(g => <EDBookGloss gloss={g}
                                                                language={section.language}
                                                                key={g.id} 
                                                                disableTools={true}
                                                                onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />)}
                        </div>
                    </div>
                ) : '')}
            </div>
        </div>;
    }
}

const mapStateToProps = (state) => {
    return {
        fragments: state.fragments,
        latin: state.latin,
        tengwar: state.tengwar,
        fragmentId: state.fragmentId,
        bookData: state.bookData,
        loading: state.loading
    };
};

EDFragmentExplorer.defaultProps = {
    stateInUrl: true
}

export default connect(mapStateToProps)(EDFragmentExplorer);
