import React from 'react';
import { connect } from 'react-redux';
import { setSelection, beginNavigation } from '../actions';
import classNames from 'classnames';

class EDSearchResults extends React.Component {

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
                <div className={classNames('row', 'search-result-navigator', {'hidden': this.props.items.length < 1})}>
                    <nav>
                        <ul className="pager">
                            <li className="previous"><a href="#" onClick={ev => this.onNavigate(ev, previousIndex)}>← {this.props.items[previousIndex].word}</a></li>
                            <li className="next"><a href="#" onClick={ev => this.onNavigate(ev, nextIndex)}>{this.props.items[nextIndex].word} →</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        );
    }
}

class EDSearchItem extends React.Component {
    navigate(ev) {
        ev.preventDefault();
        this.props.onNavigate(this.props.index, this.props.item.word, this.props.item.normalizedWord);
    }

    render() {
        const cssClass = classNames({ 'selected': this.props.active });
        return <li>
            <a href="#" className={cssClass} onClick={this.navigate.bind(this)}>
                {this.props.item.word}
            </a>
        </li>;
    }
}

const mapStateToProps = (state) => {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex
    };
};

export default connect(mapStateToProps)(EDSearchResults);
