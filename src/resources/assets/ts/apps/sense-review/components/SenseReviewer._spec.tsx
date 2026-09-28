import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import {
    beforeAll,
    describe,
    expect,
    jest,
    test,
} from '@jest/globals';

import { GlobalEventLoadReference } from '@root/config';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';

import type ISenseApi from '@root/connectors/backend/ISenseApi';
import type { IConceptSuggestion } from '@root/connectors/backend/ISenseApi';
import type ISenseReviewApi from '@root/connectors/backend/ISenseReviewApi';
import type { IDecision, ISenseUnderReview } from '@root/connectors/backend/ISenseReviewApi';

import SenseReviewer from './SenseReviewer';

const sense: ISenseUnderReview = {
    senseId: 42,
    sense: 'light, radiance',
    headword: 'light',
    reason: 'unsure',
    detail: 'synonym bright',
    confidence: 55,
    entries: 12,
    viaPhraseHead: false,
    speeches: [ 'noun' ],
    usages: [
        {
            lexicalEntryId: 907,
            word: 'alda',
            language: 'Quenya',
            speech: 'noun',
            glosses: 'tree',
            url: '/lexical-entry/907',
        },
    ],
    candidates: [
        {
            synsetId: '11473954-n',
            label: 'light',
            pos: 'noun',
            lexname: 'noun.phenomenon',
            definition: 'electromagnetic radiation that can produce a visual sensation',
            synonyms: [ 'visible light' ],
            lineage: [ 'radiation' ],
        },
        {
            synsetId: '03667792-n',
            label: 'lamp',
            pos: 'noun',
            lexname: 'noun.artifact',
            definition: 'a piece of furniture holding one or more electric light bulbs',
            synonyms: [],
            lineage: [ 'furniture' ],
        },
    ],
};

const senseApi: ISenseApi = {
    find: () => Promise.resolve({ concepts: [], senses: [] }),
};

/** What searching "this" really returns: nothing named that, only words beginning with it. */
const thistle: IConceptSuggestion = {
    definition: 'any of numerous plants of the family Compositae',
    entries: 2,
    id: 991,
    label: 'thistle',
    lineage: [ 'weed', 'vascular plant' ],
    named: false,
    synonyms: [],
};

/** Rewording is its own test; everywhere else it simply must exist. */
const neverReworded = () => jest.fn<ISenseReviewApi['reword']>();

/**
 * An API whose queue holds one sense, then nothing, so a decision is followed by an empty queue.
 */
const apiWith = (decide: (senseId: number, decision: IDecision) => void): ISenseReviewApi => {
    let decided = false;

    return {
        next: () => Promise.resolve({
            sense: decided ? null : sense,
            waiting: decided ? 0 : 1,
            byReason: decided ? {} : { unsure: 1 },
        }),
        decide: (senseId: number, decision: IDecision) => {
            decided = true;
            decide(senseId, decision);

            return Promise.resolve({ assigned: true, outcome: 'assigned', waiting: 0 });
        },
        reword: neverReworded(),
    };
};

/** The wording shows twice — the card's heading and the bar that keeps it in sight — so ask for the heading. */
const senseHeading = (wording: string) => screen.findByRole('heading', { name: wording });

describe('apps/sense-review/SenseReviewer', () => {
    beforeAll(() => {
        setInstance(DI.GlobalEvents, GlobalEventConnector);
    });

    test('tapping an entry opens it in the glossary rather than leaving the page', async () => {
        render(<SenseReviewer api={apiWith(() => undefined)} senseApi={senseApi} reasons={{}} />);
        await senseHeading('light, radiance');

        const opened: number[] = [];
        const onLoadReference = (ev: Event) => opened.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        const entry = screen.getByRole('link', { name: 'alda' });
        // false when the click's preventDefault() was called, which is what stops the navigation
        const navigated = fireEvent.click(entry, { button: 0 });

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(opened).toEqual([ 907 ]);
        expect(navigated).toBe(false);
        // still a real link, so opening it in a new tab works as it always did
        expect(entry.getAttribute('href')).toEqual('/lexical-entry/907');
    });

    test('shows the sense, why it waits, and the meanings on offer', async () => {
        render(<SenseReviewer api={apiWith(() => undefined)} senseApi={senseApi} reasons={{ unsure: 'Not sure.' }} />);

        expect(await senseHeading('light, radiance')).toBeTruthy();
        expect(screen.getByText(/Quenya, noun/)).toBeTruthy();
        expect(screen.getByText('lamp')).toBeTruthy();
        expect(screen.getByText(/Not sure\./)).toBeTruthy();
        expect(screen.getByText('How this works')).toBeTruthy();
        expect(screen.getByText('synonym bright')).toBeTruthy();
    });

    test('records the meaning the editor picks, with what was on offer', async () => {
        const decisions: { senseId: number; decision: IDecision }[] = [];
        render(<SenseReviewer api={apiWith((senseId, decision) => decisions.push({ senseId, decision }))}
            senseApi={senseApi} reasons={{}} />);

        await senseHeading('light, radiance');
        // by role, not by text: the guide names the buttons too
        fireEvent.click(screen.getAllByRole('button', { name: 'Means this' })[1]);

        await waitFor(() => expect(decisions.length).toEqual(1));
        expect(decisions[0].senseId).toEqual(42);
        expect(decisions[0].decision.synsetId).toEqual('03667792-n');
        expect(decisions[0].decision.relation).toEqual('synonym');
        expect(decisions[0].decision.offered).toEqual([ '11473954-n', '03667792-n' ]);
    });

    test('a sense that means nothing the taxonomy holds is dismissed', async () => {
        const decisions: IDecision[] = [];
        render(<SenseReviewer api={apiWith((_, decision) => decisions.push(decision))}
            senseApi={senseApi} reasons={{}} />);

        await senseHeading('light, radiance');
        fireEvent.click(screen.getByRole('button', { name: /Not a meaning/ }));

        await waitFor(() => expect(decisions.length).toEqual(1));
        expect(decisions[0].dismiss).toEqual(true);
    });

    test('skipping asks for the next sense without deciding anything', async () => {
        const next = jest.fn<ISenseReviewApi['next']>(() => Promise.resolve({
            sense, waiting: 1, byReason: { unsure: 1 },
        }));
        const decide = jest.fn<ISenseReviewApi['decide']>();
        render(<SenseReviewer api={{ next, decide, reword: neverReworded() }} senseApi={senseApi} reasons={{}} />);

        await senseHeading('light, radiance');
        // the button carries its shortcut in its name, so match the word alone
        fireEvent.click(screen.getByRole('button', { name: /Skip/ }));

        await waitFor(() => expect(next.mock.calls.length).toEqual(2));
        expect(next.mock.calls[1][0]).toEqual([ 42 ]);
        expect(decide.mock.calls.length).toEqual(0);
    });

    test('the next sense takes its place once a decision is recorded', async () => {
        const second: ISenseUnderReview = { ...sense, senseId: 43, sense: 'gate, door', headword: 'gate' };
        let decided = false;
        const api: ISenseReviewApi = {
            next: () => Promise.resolve({
                sense: decided ? second : sense,
                waiting: decided ? 1 : 2,
                byReason: { unsure: decided ? 1 : 2 },
            }),
            decide: () => {
                decided = true;

                return Promise.resolve({ assigned: true, outcome: 'assigned', waiting: 1 });
            },
            reword: neverReworded(),
        };
        render(<SenseReviewer api={api} senseApi={senseApi} reasons={{}} />);

        await senseHeading('light, radiance');
        fireEvent.click(screen.getAllByRole('button', { name: 'Means this' })[0]);

        expect(await senseHeading('gate, door')).toBeTruthy();
        expect(screen.queryByRole('heading', { name: 'light, radiance' })).toBeNull();
    });

    test('the search starts empty on each sense, rather than seeded with its headword', async () => {
        const find = jest.fn<ISenseApi['find']>(() => Promise.resolve({ concepts: [], senses: [] }));
        render(<SenseReviewer api={apiWith(() => undefined)} senseApi={{ find }} reasons={{}} />);

        await senseHeading('light, radiance');

        expect(screen.getByRole('searchbox').getAttribute('value')).toEqual('');
        expect(find.mock.calls.length).toEqual(0);
    });

    test('warns when nothing is named what was searched for', async () => {
        const find = jest.fn<ISenseApi['find']>(() => Promise.resolve({ concepts: [ thistle ], senses: [] }));
        render(<SenseReviewer api={apiWith(() => undefined)} senseApi={{ find }} reasons={{}} />);

        await senseHeading('light, radiance');
        fireEvent.change(screen.getByRole('searchbox'), { target: { value: 'this' } });

        expect(await screen.findByText(/only begin with it/)).toBeTruthy();
        expect(screen.getByText('thistle')).toBeTruthy();
    });

    test('n moves to the next sense, and s is left to the glossary search above', async () => {
        const next = jest.fn<ISenseReviewApi['next']>(() => Promise.resolve({
            sense, waiting: 1, byReason: { unsure: 1 },
        }));
        const decide = jest.fn<ISenseReviewApi['decide']>();
        render(<SenseReviewer api={{ next, decide, reword: neverReworded() }} senseApi={senseApi} reasons={{}} />);
        await senseHeading('light, radiance');

        // the glossary binds "s" site-wide to focus its search box; taking it here would swallow what follows
        fireEvent.keyDown(window, { key: 's' });
        expect(next.mock.calls.length).toEqual(1);

        fireEvent.keyDown(window, { key: 'n' });

        await waitFor(() => expect(next.mock.calls.length).toEqual(2));
        expect(next.mock.calls[1][0]).toEqual([ 42 ]);
        expect(decide.mock.calls.length).toEqual(0);
    });

    test('a mis-transcribed sense has its entries moved to the wording they should carry', async () => {
        const reworded: { senseId: number; wording: string }[] = [];
        const api: ISenseReviewApi = {
            ...apiWith(() => undefined),
            reword: (senseId: number, wording: string) => {
                reworded.push({ senseId, wording });

                return Promise.resolve({
                    outcome: 'reworded',
                    result: { entries: 14, refusal: null, sense: wording, senseId: 6015 },
                    waiting: 0,
                });
            },
        };
        render(<SenseReviewer api={api} senseApi={senseApi} reasons={{}} />);
        await senseHeading('light, radiance');

        const button = screen.getByRole('button', { name: /Reword 12 entries/ });
        // shut until a different wording is given, so the entries cannot be moved to where they already are
        expect(button.hasAttribute('disabled')).toBe(true);

        fireEvent.change(screen.getByRole('textbox', { name: '' }), { target: { value: 'number' } });
        fireEvent.click(screen.getByRole('button', { name: /Reword 12 entries/ }));

        await waitFor(() => expect(reworded.length).toEqual(1));
        expect(reworded[0]).toEqual({ senseId: 42, wording: 'number' });
        expect(await screen.findByText(/14 entries now read/)).toBeTruthy();
    });

    test('says so when the queue is empty', async () => {
        const api: ISenseReviewApi = {
            next: () => Promise.resolve({ sense: null, waiting: 0, byReason: {} }),
            decide: () => Promise.resolve({ assigned: false, outcome: 'left as it was', waiting: 0 }),
            reword: neverReworded(),
        };
        render(<SenseReviewer api={api} senseApi={senseApi} reasons={{}} />);

        expect(await screen.findByText(/Nothing left to review/)).toBeTruthy();
    });
});
