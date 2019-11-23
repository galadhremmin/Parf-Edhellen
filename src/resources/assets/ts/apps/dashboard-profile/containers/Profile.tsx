import React from 'react';

import Markdown from '@root/components/Markdown';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { DI, resolve } from '@root/di';
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
        statistics,
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
        {statistics && <section className="Profile--container__statistics">
            <p>
                {nickname} has flipped <em>{statistics.noOfFlashcards} flashcards</em>,
                received <em>{statistics.noOfThanks} thanks</em>,
                and created <a href={`/author/${id}/posts`}><em>{statistics.noOfPosts} posts</em></a>.
                They have contributed to the dictionary by creating{' '}
                <a href={`/author/${id}/glosses`}><em>{statistics.noOfGlosses} glosses</em></a>,{' '}
                <a href={`/author/${id}/sentences`}><em>{statistics.noOfSentences} texts</em></a>{' '}
                and <em>{statistics.noOfWords} words</em>.
            </p>
        </section>}
        {(roleManager.accountId === id || roleManager.isAdministrator) && <aside className="text-center">
            <a href={`/author/edit/${id}`} className="btn btn-default">
                <TextIcon icon="edit" />
                Edit profile
            </a>
        </aside>}
    </article>;
}

Profile.defaultProps = {
    roleManager: resolve(DI.RoleManager),
} as Partial<IProps>;

export default Profile;
