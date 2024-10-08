import Discuss from '@root/apps/discuss';
import Avatar from '@root/components/Avatar';
import Markdown from '@root/components/Markdown';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import AccountFeed from '../components/AccountFeed';
import JumbotronOrHeader from '../components/JumbotronOrHeader';
import { IProps } from './Profile._types';

import './Profile.scss';

function Profile(props: IProps) {
    const {
        avatarPath,
        id,
        featureBackgroundUrl,
        nickname,
        profile,
        tengwar,
    } = props.account;

    const {
        showProfile = false,
        roleManager,
        readonly,
        showDiscuss = false,
        showJumbotron = false,
        showProfileLink = false,
        statistics,
    } = props;

    const canModify = roleManager.accountId === id || //
        roleManager.isAdministrator;

    return <div className="Profile--container">
        <JumbotronOrHeader className={showJumbotron ? 'with-background' : ''}
            isJumbotron={showJumbotron}
            backgroundImageUrl={featureBackgroundUrl}>
            <Avatar path={avatarPath} />
            <h1>{nickname}</h1>
            {tengwar && <Tengwar as="h2" text={tengwar} />}
            <aside className="text-center">
                {showProfileLink && <a href={`/author/${id}`} className="btn btn-primary">
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
        </JumbotronOrHeader>
        <div className="container-fluid">
            <div className="row">
                {showProfile && <div className="col-md-6 col-sm-12">
                    {profile ? <Markdown parse={true} text={profile} />
                        : <p>
                            {nickname} is but a rumour in the wind. Perhaps one day they might
                            come forth and reveal themselves.
                        </p>}
                </div>}
                {statistics && <div className="col-md-6 col-sm-12">
                    <p>
                        {nickname} has flipped <em>{statistics.noOfFlashcards} flashcards</em>,
                        received <em>{statistics.noOfThanks} thanks</em>,
                        and created <a href={`/author/${id}/posts`}><em>{statistics.noOfPosts} posts</em></a>.
                        They have contributed to the dictionary by creating{' '}
                        <a href={`/author/${id}/glosses`}><em>{statistics.noOfGlosses} glosses</em></a>,{' '}
                        <a href={`/author/${id}/sentences`}><em>{statistics.noOfSentences} texts</em></a>{' '}
                        and <em>{statistics.noOfWords} words</em>.
                    </p>
                </div>}
            </div>
            {showDiscuss && <div className="row">
                <div className="col-12">
                    <h2>Messages</h2>
                    <p>These are personal messages as well as messages left by others on their profile.</p>
                    <Discuss entityId={id} entityType="account" prefetched={false} stretchUi={true} />
                </div>
            </div>}
            <div className="row">
                <div className="col-12">
                    <h2>Timeline</h2>
                    <p>Their community and dictionary activities sorted by date in descending order.</p>
                    <AccountFeed account={props.account} />
                </div>
            </div>
        </div>
    </div>;
}

export default withPropInjection(Profile, {
    roleManager: DI.RoleManager,
});
