import ProfileLink from '@root/components/ProfileLink';

import { IProps } from './Avatar._types';

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
