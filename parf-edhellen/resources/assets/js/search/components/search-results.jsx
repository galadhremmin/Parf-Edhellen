import React from 'react';
import { connect } from 'react-redux';
import { setSelection, beginNavigation } from '../actions';
import classNames from 'classnames';
import EDSearchItem from './search-item';
import EDBookSection from './book-section';

/**
 * Represents a collection of search results.
 */
class EDSearchResults extends React.Component {
    constructor() {
        super();

        this.popStateHandler = this.onPopState.bind(this);
    }

    componentWillMount() {
        window.addEventListener('popstate', this.popStateHandler);
    }

    componentWillUnmount() {
        window.removeEventListener(this.popStateHandler);
    }

    componentWillReceiveProps(props) {
        if (props.activeIndex === undefined || props.activeIndex < 0) {
            return;
        }

        const item = props.items[props.activeIndex];
        if (item.word === this.loadedWord) {
            return;
        }

        this.loadedWord = item.word;
        props.dispatch(beginNavigation(item.word, item.normalizedWord, this.loadedIndex));
    }

    navigate(index, word, normalizedWord) {
        this.props.dispatch(setSelection(index));
    }

    onNavigate(ev, index) {
        ev.preventDefault();
        this.navigate(index);
    }

    /**
     * This is an unfortunate hack which implements default behavior for the forward and back buttons.
     * This method should be connected to the _popstate_ window event. It examines the URL of the previous
     * (or forward) state and determines whether the word (if found) exists within the current search
     * result set. If it is present, it navigates to the word, and dispatches a navigation signal.
     * 
     * @param {*} ev 
     */
    onPopState(ev) {
        // The path name should be /w/<word>
        const path = location.pathname;
        if (path.substr(0, 3) !== '/w/') {
            return; // the browser is going somewhere else, so do nothing.
        }

        // retrieve the word and attempt to locate it within the search result set.
        const normalizedWord = decodeURIComponent(path.substr(3));
        const index = this.props.items
            ? this.props.items.findIndex(i => i.normalizedWord === normalizedWord)
            : -1;

        if (index > -1) {
            // Since the word exists in the search result set, update the current selection.
            // Make sure to update the _loadedWord_ property first, to cancel default behaviour
            // implemented in the _componentWillReceiveProps_ method.
            this.loadedWord = this.props.items[index].word;
            this.navigate(index);
        } 
        
        this.props.dispatch(beginNavigation(normalizedWord, undefined, undefined, false));
    }

    render() {
        if (!Array.isArray(this.props.items)) {
            return <div></div>;
        }

        let previousIndex = this.props.activeIndex - 1;
        let nextIndex = this.props.activeIndex + 1;

        if (previousIndex < 0) {
            previousIndex = this.props.items.length - 1;
        }

        if (nextIndex >= this.props.items.length - 1) {
            nextIndex = 0;
        }

        console.log(this.props.bookData);

        return (
            <div>
                <div className="panel panel-default search-result-wrapper">
                    <div className="panel-heading">
                        <h3 className="panel-title search-result-wrapper-toggler-title">
                            <span className="glyphicon glyphicon-minus"></span>
                            Matching words ({this.props.items.length})
                        </h3>
                    </div>
                    <div className={classNames('panel-body', 'results-panel', {'hidden': this.props.items.length < 1})}>
                        <div className="row">
                            <div className="col-xs-12">
                                These words match your search query. Click on the one most relevant to you,
                                or simply press enter to expand the first item in the list.
                            </div>
                        </div>
                        <div className="row">
                            <ul className="search-result">
                                {this.props.items.map((item, i) =>
                                    <EDSearchItem key={i} active={i === this.props.activeIndex}
                                                  item={item} index={i}
                                                  onNavigate={this.navigate.bind(this)} />)}
                            </ul>
                        </div>
                    </div>
                    <div className={classNames('panel-body', 'results-empty', {'hidden': this.props.items.length > 0})}>
                        <div className="row">
                            <div className="col-xs-12">
                                Unfortunately, we were unable to find any words matching your search query. Have you tried a synonym, or perhaps even an antonym?
                            </div>
                        </div>
                    </div>
                </div>
                {this.props.items.length > 1 ? (
                <div className="row search-result-navigator">
                    <nav>
                        <ul className="pager">
                            <li className="previous"><a href="#" onClick={ev => this.onNavigate(ev, previousIndex)}>← {this.props.items[previousIndex].word}</a></li>
                            <li className="next"><a href="#" onClick={ev => this.onNavigate(ev, nextIndex)}>{this.props.items[nextIndex].word} →</a></li>
                        </ul>
                    </nav>
                </div>
                ) : ''}
                {this.props.bookData ? (
                    <div className="search-result-presenter">
                        {this.props.bookData.sections.length < 1 ? (
                            <div class="row">
                                <h3>Forsooth! I can't find what you're looking for!</h3>
                                <p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
                            </div>
                        ) : (
                            <div className="row">
                                {this.props.bookData.sections.map(
                                    s => <EDBookSection section={s}
                                                        key={s.language.ID}
                                                        columnsMax={this.props.bookData.columnsMax}
                                                        columnsMid={this.props.bookData.columnsMid}
                                                        columnsMin={this.props.bookData.columnsMin}/>
                                )}
                            </div>
                        )}
                    </div>
                ): ''}
            </div>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex,
        bookData: state.bookData
    };
};

export default connect(mapStateToProps)(EDSearchResults);
