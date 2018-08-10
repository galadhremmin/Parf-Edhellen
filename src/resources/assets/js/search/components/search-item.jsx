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

    format(word) {
        return word === undefined || word === null 
            ? null 
            : word.toLocaleLowerCase();
    }

    render() {
        const cssClass = classNames({ 'selected': this.props.active });
        let { word, originalWord } = this.props.item;

        word = this.format(word);
        originalWord = this.format(originalWord);

        return <li>
            <a href="#" className={cssClass} onClick={this.navigate.bind(this)}>
                {originalWord || word}
            </a>
            {originalWord && originalWord !== word && ` ⇨ ${word}`}
        </li>;
    }
}

export default EDSearchItem;
