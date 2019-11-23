import React from 'react';

import { IProps } from './ProfileLink._types';

const ProfileLink = (props: IProps) => {
    const {
        account,
    } = props;

    if (account !== null && typeof account === 'object') {
        return <a href={`/author/${props.account.id}`}
            title={`View ${props.account.nickname}'s profile`}
            className={props.className}>
            {props.children || props.account.nickname}
        </a>;
    } else {
        // No account = no link. Render nothing.
        return null;
    }
};

export default ProfileLink;
