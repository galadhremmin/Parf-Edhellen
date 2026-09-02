import {
    afterEach,
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';
import * as sinon from 'sinon';

import { SearchResultGlossaryGroupId } from '@root/config';
import BookApiConnector from '@root/connectors/backend/BookApiConnector';

import SearchActions, { hasGlossaryChangedAddress } from '../actions/SearchActions';
import type { ISearchAction } from '../reducers/SearchReducer._types';
import Actions from './Actions';

describe('apps/book-browser/reducers/SearchReducer', () => {
    const TestSearchResults = {
        keywords: [
            {
                g: 1,
                k: 'keyword1',
                nk: 'keyword1-nk',
                ok: 'keyword1-ok',
            },
            {
                g: 1,
                k: 'keyword2',
                nk: 'keyword2-nk',
                ok: 'keyword2-ok',
            },
        ],
        searchGroups: {
            1: 'Unit test',
        },
    };

    let sandbox: sinon.SinonSandbox;
    let actions: SearchActions;

    beforeEach(() => {
        sandbox = sinon.createSandbox();

        const api = sinon.createStubInstance(BookApiConnector);
        api.find.callsFake(() => Promise.resolve(TestSearchResults));
        actions = new SearchActions(api as any, null /* LanguageConnector */, null /* global events */);
    });

    afterEach(() => {
        sandbox.restore();
    });

    describe('expandSpecificGloss', () => {
        const TestEntity = {
            entities: {
                sections: [
                    {
                        entities: [
                            {
                                id: 4711,
                                language: { shortName: 'q' },
                                normalizedWord: 'aha',
                                word: 'aha',
                            },
                        ],
                        language: { shortName: 'q' },
                    },
                ],
            },
            groupId: 0,
            single: true,
            word: 'aha',
        };

        let pushState: sinon.SinonStub;

        beforeEach(() => {
            const api = sinon.createStubInstance(BookApiConnector);
            api.entity.callsFake(() => Promise.resolve(TestEntity as any));
            actions = new SearchActions(api as any, null /* LanguageConnector */, null /* global events */);
            pushState = sandbox.stub(window.history, 'pushState');
        });

        test('pushes the entry address so the URL reflects what is on screen', async () => {
            expect(hasGlossaryChangedAddress()).toBe(false);

            await actions.expandSpecificGloss(4711)(sandbox.spy() as any);

            // The glossary now owns the address, so a back navigation onto the server-rendered page
            // it started from has to be answered with a real page load.
            expect(hasGlossaryChangedAddress()).toBe(true);

            expect(pushState.callCount).toEqual(1);
            expect(pushState.firstCall.args[2]).toEqual('/wt/4711');
            expect(pushState.firstCall.args[0]).toEqual({
                glossary: true,
                groupId: SearchResultGlossaryGroupId,
                languageShortName: 'q',
                lexicalEntryId: 4711,
                normalizedWord: 'aha',
                word: 'aha',
            });
            expect(document.title).toEqual('Aha - Parf Edhellen');
        });

        test('does not push when the load came from the back or forward button', async () => {
            await actions.expandSpecificGloss(4711, false)(sandbox.spy() as any);

            expect(pushState.callCount).toEqual(0);
            expect(document.title).toEqual('Aha - Parf Edhellen');
        });
    });

    test('searches for word', async () => {
        const fakeDispatch = sandbox.spy();

        const searchArgs: ISearchAction = { word: 'hello' };
        const action = actions.search(searchArgs);
        await action(fakeDispatch);

        expect(fakeDispatch.callCount).toEqual(2);
        expect(fakeDispatch.firstCall.args.length).toEqual(1);
        expect(fakeDispatch.firstCall.args[0]).toEqual({
            type: Actions.RequestSearchResults,
            ...searchArgs,
        });
        expect(fakeDispatch.secondCall.args.length).toEqual(1);
        expect(fakeDispatch.secondCall.args[0].type).toEqual(Actions.ReceiveSearchResults);

        const items = TestSearchResults.keywords.map((r, index) => ({
            groupId: r.g,
            id: index,
            normalizedWord: r.nk,
            originalWord: r.ok,
            word: r.k,
        }));
        const actual = fakeDispatch.secondCall.args[0].searchResults.keywords[TestSearchResults.searchGroups[1]];
        expect(actual).toEqual(items);
    });
});
