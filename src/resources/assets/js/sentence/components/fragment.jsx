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
        // string fragments, like white space?
        if (! this.props.fragment) {
            return <span>{this.props.text || ''}</span>;
        }

        const text = this.props.text || this.props.fragment.fragment;

        // interpunctuation, or other markers?
        if (this.props.fragment.type) {
            return <span>{text}</span>;
        }

        return <a className={classNames({'active': this.props.selected, 'text-danger': this.props.erroneous})}
                  href={`/wt/${this.props.fragment.translation_id}`}
                  onClick={this.onFragmentClick.bind(this)}>
                  {text}
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
    erroneous: false,
    onClick: null
};

export default EDFragment;
