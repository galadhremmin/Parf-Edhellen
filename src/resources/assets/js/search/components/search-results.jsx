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

        this.state = {
            itemsOpened: true
        };

        this.popStateHandler = this.onPopState.bind(this);
        this.messageHandler  = this.onWindowMessage.bind(this);
    }

    componentWillMount() {
        window.addEventListener('popstate', this.popStateHandler);
        window.addEventListener('message', this.messageHandler, false);
    }

    componentWillUnmount() {
        window.removeEventListener(this.popStateHandler);
        window.removeEventListener(this.messageHandler);
    }

    /**
     * Active index has changed?
     * @param props
     */
    componentWillReceiveProps(props) {
        if (props.activeIndex === undefined || props.activeIndex < 0) {
            return;
        }

        const item = props.items[props.activeIndex];
        if (item.word === this.loadedWord) {
            return;
        }

        this.loadedWord = item.word;
        props.dispatch(beginNavigation(item.word, item.normalizedWord, props.activeIndex));
    }

    navigate(index) {
        this.props.dispatch(setSelection(index));
        this.gotoResults();
    }

    gotoResults() {
        // Is the results view within the viewport?
        let results = document.getElementsByClassName('search-result-navigator');
        if (results.length < 1) {
            results = document.getElementsByClassName('search-result-presenter');
        }

        if (results.length < 1) {
            return ; // bail, as something's weird
        }

        const element = results[0];
        window.setTimeout(() => element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        }), 500);
    }

    gotoReference(normalizedWord, urlChanged) {
        // Should we consider the URL as changed? This is the case when the back-button
        // in the browser is pressed. When this method however is manually triggered,
        // this may not be the case.
        if (urlChanged === undefined) {
            urlChanged = true;
        }

        let index = this.props.items
            ? this.props.items.findIndex(i => i.normalizedWord === normalizedWord)
            : -1;

        if (index > -1) {
            // Since the word exists in the search result set, update the current selection.
            // Make sure to update the _loadedWord_ property first, to cancel default behaviour
            // implemented in the _componentWillReceiveProps_ method.
            this.loadedWord = this.props.items[index].word;
            this.navigate(index);
        } else {
            // The word does not exist in the current result set.
            index = undefined;
        }

        this.props.dispatch(beginNavigation(normalizedWord, undefined, index, !urlChanged));
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
        this.gotoReference(normalizedWord);
    }

    onPanelClick(ev) {
        ev.preventDefault();

        this.setState({
            itemsOpened: !this.state.itemsOpened
        });
    }

    onReferenceLinkClick(ev) {
        this.gotoReference(ev.word, false);
    }

    /**
     * Receives a window message and deals with known messages.
     * @param {*} ev 
     */
    onWindowMessage(ev) {
        const domain = ev.origin || ev.originalEvent.origin;
        if (domain !== window.EDConfig.messageDomain) {
            return;
        }

        const data = ev.data;
        switch (data.source) {
            case window.EDConfig.messageNavigateName:
                this.gotoReference(data.payload.word, false);
                break;
        }
    }

    renderSearchResults() {
        let previousIndex = this.props.activeIndex - 1;
        let nextIndex = this.props.activeIndex + 1;

        if (previousIndex < 0) {
            previousIndex = this.props.items.length - 1;
        }

        if (nextIndex >= this.props.items.length - 1) {
            nextIndex = 0;
        }

        return (<section>
            <div className="panel panel-default search-result-wrapper">
                <div className="panel-heading" onClick={this.onPanelClick.bind(this)}>
                    <h3 className="panel-title search-result-wrapper-toggler-title">
                                <span className={classNames('glyphicon', { 'glyphicon-minus': this.state.itemsOpened },
                                    { 'glyphicon-plus': !this.state.itemsOpened })} />
                        {` Matching words`}
                    </h3>
                </div>
                <div className={classNames('panel-body', 'results-panel',
                            {'hidden': this.props.items.length < 1 || !this.state.itemsOpened})}>
                    <div className="row">
                        <div className="col-xs-12">
                            These words match <em>{this.props.wordSearch}</em>. Click on the one most relevant to you,
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
                <div className={classNames('panel-body', 'results-empty',
                            {'hidden': this.props.items.length > 0})}>
                    <div className="row">
                        <div className="col-xs-12">
                            Unfortunately, we were unable to find any words matching <em>{this.props.wordSearch}</em>.
                            Have you tried a synonym, or perhaps even an antonym?
                        </div>
                    </div>
                </div>
                <div className={classNames('panel-body', { 'hidden': this.state.itemsOpened })}>
                    {`${this.props.items.length} matching words. Click on the title to expand.`}
                </div>
            </div>
            {this.props.items.length > 1 ? (
                <div className="row search-result-navigator">
                    <nav>
                        <ul className="pager">
                            <li className="previous"><a href="#" onClick={ev => this.onNavigate(ev, previousIndex)}>&larr; {this.props.items[previousIndex].word}</a></li>
                            <li className="next"><a href="#" onClick={ev => this.onNavigate(ev, nextIndex)}>{this.props.items[nextIndex].word} &rarr;</a></li>
                        </ul>
                    </nav>
                </div>
            ) : ''}
        </section>);
    }

    renderBook() {
        return (<section>
            <div className="search-result-presenter">
                {this.props.bookData.sections.length < 1 ? (
                    <div class="row">
                        <h3>Forsooth! I can't find what you're looking for!</h3>
                        <p>The word <em>{this.props.bookData.word}</em> hasn't been recorded for any of the languages.</p>
                    </div>
                ) : (
                    <div className="row">
                        {this.props.bookData.sections.map(
                            s => <EDBookSection section={s}
                                                key={s.language.id}
                                                columnsMax={this.props.bookData.columnsMax}
                                                columnsMid={this.props.bookData.columnsMid}
                                                columnsMin={this.props.bookData.columnsMin}
                                                onReferenceLinkClick={this.onReferenceLinkClick.bind(this)}/>
                        )}
                    </div>
                )}
            </div>
        </section>);
    }

    render() {

        let searchResults = null;
        if (Array.isArray(this.props.items)) {
            searchResults = this.renderSearchResults();
        }

        let book = null;
        if (this.props.bookData) {
            book = this.renderBook();
        }

        return (
            <div>
                {searchResults ? searchResults : ''}
                {book ? book : ''}
            </div>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex,
        bookData: state.bookData,
        wordSearch: state.wordSearch
    };
};

export default connect(mapStateToProps)(EDSearchResults);
