import React from 'react';
import classNames from 'classnames';

class EDFragment extends React.Component {
    onFragmentClick(ev) {
        ev.preventDefault();

        if (this.props.onClick) {
            this.props.onClick({
                id: this.props.fragment.id,
                url: ev.target.href,
                translationId: this.props.fragment.translateId
            });
        }
    }

    render() {
        const f = this.props.fragment;

        if (f.interpunctuation) {
            return <span>{f.fragment}</span>;
        }

        return <span>
            {' '}
            <a className={classNames({'active': this.props.selected})}
                     href={`/wt/${f.translateId}`}
                     onClick={this.onFragmentClick.bind(this)}>{f.fragment}</a>
        </span>;
    }
}

export default EDFragment;
