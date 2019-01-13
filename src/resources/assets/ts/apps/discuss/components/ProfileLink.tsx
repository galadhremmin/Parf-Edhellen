import React from 'react';

import { IProps } from './ProfileLink._types';

const ProfileLink = (props: IProps) => <a href={`/author/${props.account.id}`}
    title={`View ${props.account.nickname}'s profile`}
    className={props.className}>
    {props.children || props.account.nickname}
</a>;

export default ProfileLink;
