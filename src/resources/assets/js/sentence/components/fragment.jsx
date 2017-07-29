import React from 'react';
import classNames from 'classnames';

class EDFragment extends React.Component {
    onFragmentClick(ev) {
        ev.preventDefault();

        if (! this.props.onClick) {
            return;
        }

        const f = this.props.fragment;
        this.props.onClick({
            id: f.id,
            url: ev.target.href,
            translation_id: f.translation_id
        });
    }

    render() {
        if (! this.props.fragment) {
            return <span>{this.props.text || ''}</span>;
        }

        return <a className={classNames({'active': this.props.selected})}
                  href={`/wt/${this.props.fragment.translation_id}`}
                  onClick={this.onFragmentClick.bind(this)}>
                  {this.props.text ? this.props.text : this.props.fragment.fragment}
               </a>;
    }

    getFragment() {
        const fragments = this.props.fragments;
        return fragments[this.props.mapping[0]];
    }
}

EDFragment.defaultProps = {
    fragment: null,
    text: undefined,
    selected: false,
    onClick: null
};

export default EDFragment;
