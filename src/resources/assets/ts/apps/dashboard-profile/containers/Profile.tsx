import { useState } from 'react';

import Discuss from '@root/apps/discuss';
import Avatar from '@root/components/Avatar';
import Markdown from '@root/components/Markdown';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import AccountFeed from '../components/AccountFeed';
import JumbotronOrHeader from '../components/JumbotronOrHeader';
import ProfileWordLists from '../components/ProfileWordLists';
import WelcomeInvitation from '../components/WelcomeInvitation';
import WelcomeProgress from '../components/WelcomeProgress';
import WelcomeReminder from '../components/WelcomeReminder';
import type { IProps as ITextIconProps } from '@root/components/TextIcon._types';
import type { IWelcome, IWelcomeStep } from '../index._types';
import type { IProps } from './Profile._types';

import './Profile.scss';

const isWelcome = (value: unknown): value is IWelcome => typeof value === 'object' && value !== null
    && typeof (value as IWelcome).steps === 'object' && (value as IWelcome).steps !== null;

const CommunityIcons: Record<string, ITextIconProps['icon']> = {
    contribution: 'book',
    discuss: 'comment',
};

function Profile(props: IProps) {
    const {
        avatarPath,
        id,
        featureBackgroundUrl,
        featureBackgroundMobileUrl,
        nickname,
        profile,
        tengwar,
    } = props.account;

    const {
        accountApi,
        showProfile = false,
        roleManager,
        readonly,
        showDiscuss = false,
        showJumbotron = false,
        showProfileLink = false,
        statistics,
        wordLists,
    } = props;

    const canModify = roleManager?.accountId === id || //
        roleManager?.isAdministrator;

    // A new member's own page shows its empty spaces as invitations, each where its result will appear.
    // Blade hands a missing welcome over as an empty string, not null, so accept only the real thing.
    const [welcome, setWelcome] = useState<IWelcome | null>(isWelcome(props.welcome) ? props.welcome : null);
    const invitation = (key: string): IWelcomeStep | null => {
        const step = welcome?.steps[key];
        return step && ! step.done ? step : null;
    };
    const avatarStep = invitation('avatar');
    const backgroundStep = invitation('background');
    const nameStep = invitation('name');
    const introductionStep = invitation('introduction');
    const communitySteps = Object.entries(welcome?.steps ?? {})
        .filter(([, step]) => step.group === 'community' && ! step.done);

    // Once hidden, the welcome can be brought back while steps are left (`welcomePending` after a reload).
    const [pending, setPending] = useState<number>(typeof props.welcomePending === 'number' ? props.welcomePending : 0);

    const hideWelcome = () => {
        setPending(welcome ? welcome.total - welcome.done : 0);
        setWelcome(null);
        void accountApi?.dismissWelcome();
    };

    const showWelcome = async () => {
        const restored = await accountApi?.restoreWelcome();
        if (isWelcome(restored)) {
            setWelcome(restored);
        }
        setPending(0);
    };

    return <div className="Profile--container">
        <JumbotronOrHeader className={showJumbotron ? 'with-background' : ''}
            isJumbotron={showJumbotron}
            backgroundImageUrl={featureBackgroundUrl}
            backgroundMobileImageUrl={featureBackgroundMobileUrl}>
            {backgroundStep && <a href={backgroundStep.url} className="Profile--invite-background">
                <TextIcon icon="plus-sign" />{' '}{backgroundStep.action}
            </a>}
            <Avatar path={avatarPath}>
                {avatarStep && <a href={avatarStep.url} className="Profile--invite-avatar" title={avatarStep.text}>
                    <TextIcon icon="plus-sign" />
                    <span>{avatarStep.action}</span>
                </a>}
            </Avatar>
            <h1>{nickname}</h1>
            {nameStep && <a href={nameStep.url} className="Profile--invite-name" title={nameStep.text}>
                <TextIcon icon="edit" />{' '}{nameStep.action}
            </a>}
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
        {welcome && <WelcomeProgress done={welcome.done} total={welcome.total} onHide={hideWelcome} />}
        {! welcome && pending > 0 && <WelcomeReminder pending={pending} onShow={() => void showWelcome()} />}
        <div className="container-fluid">
            <div className="row">
                {showProfile && <div className="col-md-6 col-sm-12">
                    {introductionStep ? <WelcomeInvitation step={introductionStep} icon="edit" />
                        : profile ? <Markdown parse={true} text={profile} />
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
            {communitySteps.length > 0 && <section className="Profile--join-in">
                <h2>Join in</h2>
                <div className="Profile--join-in-invitations">
                    {communitySteps.map(([key, step]) => <WelcomeInvitation key={key} step={step}
                        icon={CommunityIcons[key]} />)}
                </div>
            </section>}
            <ProfileWordLists nickname={nickname} wordLists={wordLists || []} />
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
    accountApi: DI.AccountApi,
    roleManager: DI.RoleManager,
});
