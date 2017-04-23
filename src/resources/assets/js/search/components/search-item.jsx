import React from 'react';
import classNames from 'classnames';

/**
 * Represents a single search result item.
 */
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

export default EDSearchItem;
