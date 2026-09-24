import {
    afterEach,
    beforeEach,
    describe,
    expect,
    jest,
    test,
} from '@jest/globals';

import type IDiscussApi from '@root/connectors/backend/IDiscussApi';
import type { IThreadResponse } from '@root/connectors/backend/IDiscussApi';
import { setSingleton } from '@root/di';
import { DI } from '@root/di/keys';
import BrowserHistory from '@root/utilities/BrowserHistory';

import DiscussActions from './DiscussActions';

const TestThread = {
    currentPage: 3,
    jumpPostId: null,
    posts: [],
    thread: {
        entityId: 1234,
        entityType: 'lex_entry_ver',
        id: 42,
    },
} as unknown as IThreadResponse;

function api(): IDiscussApi {
    return {
        thread: jest.fn(async () => TestThread),
    } as unknown as IDiscussApi;
}

describe('apps/discuss/actions/DiscussActions', () => {
    let pushState: jest.SpiedFunction<typeof window.history.pushState>;

    beforeEach(() => {
        setSingleton(DI.BrowserHistory, BrowserHistory);
        pushState = jest.spyOn(window.history, 'pushState').mockImplementation(() => undefined);
    });

    afterEach(() => {
        pushState.mockRestore();
    });

    /**
     * Dispatches a thunk, and every thunk it dispatches in turn, so the whole chain runs.
     */
    async function load(actions: DiscussActions) {
        // no posts in the response, so the metadata thunk this chain ends in bails out at once
        const getState = () => ({ threadMetadatas: {} });
        const dispatch = async (action: unknown) => {
            if (typeof action === 'function') {
                await (action as (d: unknown, s: unknown) => Promise<void> | void)(dispatch, getState);
            }
        };

        await dispatch(actions.thread({ entityId: 1234, entityType: 'lex_entry_ver' }));
    }

    test('loading a thread rewrites the address by default', async () => {
        await load(new DiscussActions(api()));

        expect(pushState).toHaveBeenCalled();
        expect(String(pushState.mock.calls[0][2])).toContain('offset=3');
    });

    test('an embedded widget leaves the address alone', async () => {
        // the versions page mounts one widget per version; each rewrite would be a history entry
        await load(new DiscussActions(api(), /* updatesHistory: */ false));

        expect(pushState).not.toHaveBeenCalled();
    });
});
