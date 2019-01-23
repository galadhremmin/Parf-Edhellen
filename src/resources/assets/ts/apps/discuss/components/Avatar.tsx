import React from 'react';

import { IProps } from './Avatar._types';
import ProfileLink from './ProfileLink';

const Avatar = (props: IProps) => {
    const account = props.account;

    return <ProfileLink account={account}>
        <img src={account.hasAvatar
            ? account.avatarPath
            : `/img/anonymous-profile-picture.png`}
        />
    </ProfileLink>;
};

export default Avatar;
