import React from 'react';

import Markdown from '@root/components/Markdown';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { RoleManager } from '@root/security';
import SharedReference from '@root/utilities/SharedReference';
import Avatar from '../components/Avatar';
import { IProps } from './Profile._types';

import './Profile.scss';

function Profile(props: IProps) {
    const {
        avatarPath,
        id,
        nickname,
        profile,
        tengwar,
    } = props.account;

    const {
        roleManager,
    } = props;

    return <article className="Profile--container">
        <header className="text-center">
            <Avatar path={avatarPath} />
            <h1>{nickname}</h1>
            {tengwar && <Tengwar as="h2" text={tengwar} />}
        </header>
        <section>
            {profile ? <Markdown parse={true} text={profile} />
                     : <p>
                        {nickname} is but a rumour in the wind. Perhaps one day they might
                        come forth and reveal themselves.
                     </p>}
        </section>
        {roleManager.accountId === id || roleManager.isAdministrator && <a
            href="{{ route('author.edit-profile') }}" className="btn btn-default">
            <TextIcon icon="edit" />
            Edit profile
        </a>}
    </article>;
}

Profile.defaultProps = {
    roleManager: SharedReference.getInstance(RoleManager),
} as Partial<IProps>;

export default Profile;
