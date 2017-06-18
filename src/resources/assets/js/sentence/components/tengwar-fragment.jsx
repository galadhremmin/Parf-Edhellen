import React from 'react';
import classNames from 'classnames';

class EDTengwarFragment extends React.Component {
    render() {
        const f = this.props.fragment;
        const previousF = this.props.previousFragment;

        if (f.tengwar === null || f.tengwar === undefined) {
            return <span className="null-tengwa"></span>;
        }

        return <span className={classNames({'active': this.props.selected})}>
            { (f.interpunctuation 
                ? '' 
                : (previousF && previousF.is_dot ? '' : ' ')
              ) + f.tengwar }
        </span>;
    }
}

export default EDTengwarFragment;
