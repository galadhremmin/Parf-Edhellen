import React from 'react';
import { connect } from 'react-redux';
import { beginNavigation } from '../actions';
import classNames from 'classnames';

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

class EDSearchResultApp extends React.Component {
    navigate(index, word, normalizedWord) {
        this.props.dispatch(setSelection(index));
    }

    componentWillReceiveProps(props) {
        if (props.activeIndex > -1 && props.activeIndex !== this.loadedIndex) {
            this.loadedIndex = props.activeIndex;

            const item = props.items[this.loadedIndex];
            props.dispatch(beginNavigation(item.word, item.normalizedWord, this.loadedIndex));

            console.log(props.activeIndex);
        }
    }

    render() {
        if (this.props.items === undefined) {
            return <div></div>;
        }

        return (
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
        );
    }
};

const mapStateToProps = (state) => {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex
    };
};

export default connect(mapStateToProps)(EDSearchResultApp);
