import React from 'react';
import classNames from 'classnames';

class EDTengwarFragment extends React.Component {
    render() {
        const f = this.props.fragment;

        return <span className={classNames({'active': this.props.selected})}>
            { (f.interpunctuation ? '' : ' ') + f.tengwar }
        </span>;
    }
}

export default EDTengwarFragment;
