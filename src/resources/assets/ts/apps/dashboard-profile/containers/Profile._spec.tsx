import { fireEvent, render, screen } from '@testing-library/react';
import {
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';

import type { IAccountEntity } from '@root/connectors/backend/IGlossResourceApi';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';
import type IRoleManager from '@root/security/IRoleManager';
import type { IWelcome } from '../index._types';
import Profile from './Profile';

/** How often the hints were dismissed and brought back, across every resolved instance. */
let dismissals = 0;
let restorations = 0;
let restoredWelcome: IWelcome | null = null;

/** The timeline asks for a feed; an empty one keeps it quiet. */
class MockedAccountApi {
    public dismissWelcome(): Promise<void> {
        dismissals += 1;
        return Promise.resolve();
    }

    public restoreWelcome(): Promise<IWelcome | null> {
        restorations += 1;
        return Promise.resolve(restoredWelcome);
    }

    public getFeed() {
        return new Promise(() => undefined);
    }
}

describe('apps/dashboard-profile/containers/Profile', () => {
    const account = { id: 7, nickname: 'Account 2503', profile: '' } as unknown as IAccountEntity;
    const roleManager = { accountId: 7, isAdministrator: false } as unknown as IRoleManager;

    const step = (group: 'profile' | 'community', title: string, done = false) => ({
        group, title, text: `${title}, please.`, action: `Do: ${title}`, url: `/${title}`, done,
    });

    const makeWelcome = (): IWelcome => ({
        done: 1,
        total: 6,
        steps: {
            name: step('profile', 'name'),
            avatar: step('profile', 'avatar'),
            introduction: step('profile', 'introduction'),
            background: step('profile', 'background', true),
            contribution: step('community', 'contribution'),
            discuss: step('community', 'discuss'),
        },
    });

    beforeEach(() => {
        dismissals = 0;
        restorations = 0;
        restoredWelcome = null;
        setInstance(DI.AccountApi, MockedAccountApi);
    });

    const renderProfile = (welcome: IWelcome | null | string, welcomePending = 0) => render(
        <Profile container="Profile" account={account} welcome={welcome as IWelcome | null} welcomePending={welcomePending}
            showProfile={true} showJumbotron={true} roleManager={roleManager} />,
    );

    test('shows each unfinished step where its result will appear', () => {
        const { container } = renderProfile(makeWelcome());

        expect(container.querySelector('.Profile--invite-avatar')?.getAttribute('href')).toEqual('/avatar');
        expect(container.querySelector('.Profile--invite-name')?.getAttribute('href')).toEqual('/name');
        expect(screen.getByText('introduction')).toBeTruthy(); // the introduction invitation's title
        expect(screen.getByText('Join in')).toBeTruthy();
        expect(screen.getByText('1 of 6 · Your page is taking shape')).toBeTruthy();
    });

    test('leaves out the steps that are done', () => {
        const { container } = renderProfile(makeWelcome());

        expect(container.querySelector('.Profile--invite-background')).toBeNull();
    });

    test('shows no hints without a welcome', () => {
        const { container } = renderProfile(null);

        expect(container.querySelector('.Profile--invite-avatar')).toBeNull();
        expect(container.querySelector('.WelcomeProgress')).toBeNull();
        expect(screen.queryByText('Join in')).toBeNull();
    });

    test('copes with the empty string Blade sends when there is no welcome', () => {
        const { container } = renderProfile('');

        expect(container.querySelector('.Profile--invite-avatar')).toBeNull();
        expect(container.querySelector('.WelcomeProgress')).toBeNull();
    });

    test('hiding the hints removes them and tells the server', () => {
        const { container } = renderProfile(makeWelcome());

        fireEvent.click(screen.getByText('Hide these hints'));

        expect(dismissals).toEqual(1);
        expect(container.querySelector('.Profile--invite-avatar')).toBeNull();
        expect(screen.queryByText('Join in')).toBeNull();
    });

    test('after hiding, the hints can be brought back while steps are left', async () => {
        restoredWelcome = makeWelcome();
        const { container } = renderProfile(makeWelcome());

        fireEvent.click(screen.getByText('Hide these hints'));
        expect(screen.getByText('5 things left to make this page yours')).toBeTruthy();

        fireEvent.click(screen.getByText('Show me'));

        expect(restorations).toEqual(1);
        expect(await screen.findByText('Hide these hints')).toBeTruthy();
        expect(container.querySelector('.Profile--invite-avatar')).toBeTruthy();
    });

    test('offers the way back on a later visit', () => {
        renderProfile('', 2);
        expect(screen.getByText('2 things left to make this page yours')).toBeTruthy();
    });

    test('a member who never hid it is offered nothing', () => {
        renderProfile('', 0);
        expect(screen.queryByText(/left to make this page yours/)).toBeNull();
    });
});
