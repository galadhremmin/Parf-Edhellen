import Avatar from '@root/components/Avatar';
import Markdown from '@root/components/Markdown';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { DI, resolve } from '@root/di';
import { IProps } from './Profile._types';

import './Profile.scss';
import JumbotronOrHeader from '../components/JumbotronOrHeader';

function Profile(props: IProps) {
    const {
        avatarPath,
        id,
        featureBackgroundFile,
        nickname,
        profile,
        tengwar,
    } = props.account;

    const {
        hideProfile,
        roleManager,
        readonly,
        statistics,
        viewJumbotron,
        viewProfileLink,
    } = props;

    const canModify = roleManager.accountId === id || //
        roleManager.isAdministrator;

    return <article className="Profile--container">
        <JumbotronOrHeader className={viewJumbotron ? 'with-background' : ''}
            isJumbotron={viewJumbotron}
            backgroundImageUrl={featureBackgroundFile}>
            <Avatar path={avatarPath} />
            <h1>{nickname}</h1>
            {tengwar && <Tengwar as="h2" text={tengwar} />}
        </JumbotronOrHeader>
        {! hideProfile && <section>
            {profile ? <Markdown parse={true} text={profile} />
                     : <p>
                        {nickname} is but a rumour in the wind. Perhaps one day they might
                        come forth and reveal themselves.
                     </p>}
        </section>}
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
        <aside className="text-center">
            {viewProfileLink && <a href={`/author/${id}`} className="btn btn-primary">
                <TextIcon icon="person" />{' '}
                View your profile
            </a>}
            {' '}
            {(! readonly && canModify) && 
            <a href={`/author/edit/${id}`} className="btn btn-secondary">
                <TextIcon icon="edit" />{' '}
                Change your profile
            </a>}
        </aside>
    </article>;
}

Profile.defaultProps = {
    roleManager: resolve(DI.RoleManager),
    viewJumbotron: false,
    viewProfileLink: false,
} as Partial<IProps>;

export default Profile;
