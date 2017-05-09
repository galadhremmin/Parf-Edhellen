import React from 'react';
import classNames from 'classnames';

class EDFragment extends React.Component {
    onFragmentClick(ev) {
        ev.preventDefault();

        if (this.props.onClick) {
            this.props.onClick({
                id: this.props.fragment.id,
                url: ev.target.href,
                translation_id: this.props.fragment.translation_id
            });
        }
    }

    render() {
        const f = this.props.fragment;
        const previousF = this.props.previousFragment;

        if (f.interpunctuation) {
            return <span>{f.fragment}</span>;
        }

        return <span>
            {previousF && previousF.is_dot ? '' : ' '}
            <a className={classNames({'active': this.props.selected})}
                     href={`/wt/${f.translation_id}`}
                     onClick={this.onFragmentClick.bind(this)}>{f.fragment}</a>
        </span>;
    }
}

export default EDFragment;
