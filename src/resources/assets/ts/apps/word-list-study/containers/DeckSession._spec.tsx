import {
    afterEach,
    beforeAll,
    describe,
    expect,
    jest,
    test,
} from '@jest/globals';
import { fireEvent, render, screen, waitFor } from '@testing-library/react';

import { GlobalEventLoadReference } from '@root/config';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';

import type {
    FlashcardDirection,
    IFlashcardAnswer,
    IFlashcardCard,
    IFlashcardDeck,
    IFlashcardDeckResponse,
    IFlashcardOption,
    IFlashcardResults,
    IFlashcardResultsResponse,
    IFlashcardSkipped,
    IWordListApi,
} from '@root/connectors/backend/IWordListApi';
import { DeckSession } from './DeckSession';

interface IDeckCall {
    wordListId: number;
    direction: FlashcardDirection;
    limit?: number;
    lexicalEntryIds?: number[];
}

interface IResultsCall {
    wordListId: number;
    direction: FlashcardDirection;
    answers: IFlashcardAnswer[];
}

const option = (key: string, text: string): IFlashcardOption => ({
    key,
    tengwar: null,
    text,
});

const card = (
    lexicalEntryId: number,
    word: string,
    options: IFlashcardOption[],
    correctOptionKey: string,
): IFlashcardCard => {
    const correctOption = options.find((o) => o.key === correctOptionKey);

    return {
        back: {
            answer: correctOption.text,
            comments: null,
            correctOptionKey,
            source: null,
            translations: [ correctOption.text ],
            url: `/w/${word}`,
            word,
        },
        cardId: `card-${lexicalEntryId}`,
        glossId: lexicalEntryId * 10,
        lexicalEntryId,
        options,
        prompt: word,
        promptTengwar: null,
    };
};

const deckOf = (cards: IFlashcardCard[], skipped: IFlashcardSkipped[] = [], numberOfRequested?: number): IFlashcardDeck => ({
    cards,
    direction: 'forward',
    numberOfRequested: numberOfRequested ?? cards.length + skipped.length,
    optionCount: cards.length > 0 ? cards[0].options.length : 0,
    skipped,
    wordListId: 42,
    wordListName: 'Unit test list',
});

/** Scores the buffered answers the way the real server does. */
const serverResults = (deck: IFlashcardDeck, answers: IFlashcardAnswer[]): IFlashcardResults => {
    const cards = answers.map((answer) => {
        const match = deck.cards.find((c) => c.lexicalEntryId === answer.lexicalEntryId);

        return {
            actual: answer.answer,
            correct: match.back.answer === answer.answer,
            expected: match.back.answer,
            lexicalEntryId: answer.lexicalEntryId,
            url: match.back.url,
            word: match.back.word,
        };
    });

    const numberOfCorrect = cards.filter((c) => c.correct).length;

    return {
        cards,
        numberOfCorrect,
        numberOfWrong: cards.length - numberOfCorrect,
    };
};

class MockedWordListApi {
    public deckCalls: IDeckCall[] = [];
    public resultsCalls: IResultsCall[] = [];

    private _decks: IFlashcardDeck[];

    constructor(decks: IFlashcardDeck[]) {
        this._decks = decks;
    }

    public deck(wordListId: number, direction: FlashcardDirection, limit?: number, lexicalEntryIds?: number[]): Promise<IFlashcardDeckResponse> {
        this.deckCalls.push({ direction, lexicalEntryIds, limit, wordListId });
        const deck = this._decks[Math.min(this.deckCalls.length - 1, this._decks.length - 1)];
        return Promise.resolve({ deck });
    }

    public deckResults(wordListId: number, direction: FlashcardDirection, answers: IFlashcardAnswer[]): Promise<IFlashcardResultsResponse> {
        this.resultsCalls.push({ answers, direction, wordListId });
        const deck = this._decks[Math.min(this.deckCalls.length - 1, this._decks.length - 1)];
        return Promise.resolve({ results: serverResults(deck, answers) });
    }
}

const renderSession = (api: MockedWordListApi, direction: FlashcardDirection = 'forward') => render(<DeckSession
    api={api as unknown as IWordListApi}
    direction={direction}
    wordListId={42}
    wordListName="Unit test list"
/>);

const clickText = (text: string) => {
    fireEvent.click(screen.getByText(text));
};

describe('apps/word-list-study/containers/DeckSession', () => {
    beforeAll(() => {
        setInstance(DI.GlobalEvents, GlobalEventConnector);
    });

    afterEach(() => {
        jest.restoreAllMocks();
    });

    test('scores a correct answer by option key, without a network round-trip', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        await screen.findByText('man');
        clickText('man');

        // The card flips locally: no results request has been made yet.
        expect(await screen.findByText(/That's right!/)).toEqual(expect.anything());
        expect(api.resultsCalls.length).toEqual(0);

        clickText('See your results');

        await screen.findByText('How did you do?');
        expect(api.resultsCalls.length).toEqual(1);
        expect(api.resultsCalls[0].answers).toEqual([
            { answer: 'man', glossId: 10, lexicalEntryId: 1 },
        ]);
        expect(screen.getByText('Correct').nextElementSibling.textContent).toEqual('1');
        expect(screen.getByText('Wrong').nextElementSibling.textContent).toEqual('0');
    });

    test('turns the card back over instead of remounting it between cards', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
            card(2, 'edhel', [ option('a', 'elf'), option('b', 'man') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        const { container } = renderSession(api);

        await screen.findByText('man');
        const flipperBefore = container.querySelector('.flipper');

        clickText('man');
        await screen.findByText(/That's right!/);
        expect(container.querySelector('.flip-container').className).toContain('flipped');

        clickText('Next card');
        await screen.findByText('edhel');

        // Same DOM node, so the CSS transition has a previous transform to
        // animate away from. A remount would snap the card to the front with
        // no rotation at all -- which is precisely the bug this guards.
        expect(container.querySelector('.flipper')).toBe(flipperBefore);
        expect(container.querySelector('.flip-container').className)
            .not.toContain('flipped');
    });

    test('keeps the answered card on the back face while it turns back over', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
            card(2, 'edhel', [ option('a', 'elf'), option('b', 'man') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        const { container } = renderSession(api);

        await screen.findByText('man');
        clickText('man');
        await screen.findByText(/That's right!/);

        clickText('Next card');
        await screen.findByText('edhel');

        // The back is the side facing the viewer for the first half of the
        // rotation, so it must still show the card that was just answered
        // rather than emptying the instant the flip starts.
        const back = container.querySelector('.back');
        expect(back.textContent).toContain('adan');
        expect(back.textContent).toContain("That's right!");
    });

    test('opens the dictionary entry in place rather than navigating away', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        await screen.findByText('man');
        clickText('man');
        await screen.findByText(/That's right!/);

        const link = screen.getByText('Open the dictionary entry') as HTMLAnchorElement;
        // The href survives, so middle click and open-in-new-tab still work.
        expect(link.getAttribute('href')).toEqual('/w/adan');

        const received: number[] = [];
        const onLoadReference = (ev: Event) => received.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        const notCancelled = fireEvent.click(link, { button: 0 });

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(received).toEqual([ 1 ]);
        // fireEvent returns false once preventDefault has been called, which is
        // what stops the browser following the href and dropping the session.
        expect(notCancelled).toBe(false);
    });

    test('lets a modified click through so it can open in a new tab', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        await screen.findByText('man');
        clickText('man');
        await screen.findByText(/That's right!/);

        const received: number[] = [];
        const onLoadReference = (ev: Event) => received.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        const notCancelled = fireEvent.click(
            screen.getByText('Open the dictionary entry'), { button: 0, ctrlKey: true }
        );

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(received).toEqual([]);
        expect(notCancelled).toBe(true);
    });

    test('scores an incorrect answer and lists the missed word', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        await screen.findByText('elf');
        clickText('elf');

        expect(await screen.findByText(/Not quite\./)).toEqual(expect.anything());

        clickText('See your results');

        await screen.findByText('How did you do?');
        expect(screen.getByText('Wrong').nextElementSibling.textContent).toEqual('1');

        const link = screen.getByText('adan') as HTMLAnchorElement;
        expect(link.getAttribute('href')).toEqual('/w/adan');
        expect(screen.getByText('man')).toEqual(expect.anything());
    });

    test('gives options that share the same text distinct keys and identities', async () => {
        const consoleError = jest.spyOn(console, 'error').mockImplementation(() => undefined);

        // Two options carry the identical text; only the second one is correct.
        const deck = deckOf([
            card(1, 'adan', [
                option('a', 'elf'),
                option('b', 'man'),
                option('c', 'man'),
            ], 'c'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        const { container } = renderSession(api);

        await screen.findByText('elf');

        const items = container.querySelectorAll('.word-list-study--option');
        expect(items.length).toEqual(3);

        // React never complained about duplicate keys.
        const duplicateKeyWarnings = consoleError.mock.calls
            .filter((call) => String(call[0]).includes('same key'));
        expect(duplicateKeyWarnings.length).toEqual(0);

        // Clicking the *second* of the two identically-labelled options counts
        // as that option, not as the first one.
        fireEvent.click(items[2].querySelector('a'));

        expect(await screen.findByText(/That's right!/)).toEqual(expect.anything());
    });

    test('registers the first of two identically-labelled options as its own option', async () => {
        const deck = deckOf([
            card(1, 'adan', [
                option('a', 'man'),
                option('b', 'man'),
            ], 'b'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        const { container } = renderSession(api);

        await screen.findByText('adan');

        const items = container.querySelectorAll('.word-list-study--option');
        fireEvent.click(items[0].querySelector('a'));

        expect(await screen.findByText(/Not quite\./)).toEqual(expect.anything());
    });

    test('retries with exactly the missed lexical entry ids', async () => {
        const firstDeck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
            card(2, 'edhel', [ option('a', 'elf'), option('b', 'man') ], 'a'),
        ]);
        const retryDeck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
        ]);
        const api = new MockedWordListApi([ firstDeck, retryDeck ]);

        renderSession(api);

        // Card 1: wrong.
        await screen.findByText('adan');
        clickText('elf');
        await screen.findByText(/Not quite\./);
        clickText('Next card');

        // Card 2: right.
        await screen.findByText('edhel');
        clickText('elf');
        await screen.findByText(/That's right!/);
        clickText('See your results');

        await screen.findByText('How did you do?');
        clickText('Retry missed words');

        await waitFor(() => {
            expect(api.deckCalls.length).toEqual(2);
        });
        expect(api.deckCalls[1].lexicalEntryIds).toEqual([ 1 ]);
        expect(api.deckCalls[1].limit).toEqual(1);
        expect(api.deckCalls[1].direction).toEqual('forward');
    });

    test('reports a short deck instead of silently dealing fewer cards', async () => {
        const deck = deckOf(
            [ card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a') ],
            [
                { lexicalEntryId: 7, reason: 'no-translation', word: 'lhach' },
                { lexicalEntryId: 8, reason: 'no-translation', word: 'naur' },
                { lexicalEntryId: 9, reason: 'no-distractors', word: 'aur' },
            ],
            4,
        );
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        const notice = await screen.findByText(/4 requested, 1 dealt/);
        expect(notice.textContent).toEqual(
            '4 requested, 1 dealt — 2 words have no usable translation, 1 word has too few similar words to choose between.',
        );
    });

    test('flushes buffered answers when the page is hidden mid-session', async () => {
        const deck = deckOf([
            card(1, 'adan', [ option('a', 'man'), option('b', 'elf') ], 'a'),
            card(2, 'edhel', [ option('a', 'elf'), option('b', 'man') ], 'a'),
        ]);
        const api = new MockedWordListApi([ deck ]);

        renderSession(api);

        await screen.findByText('adan');
        clickText('man');
        await screen.findByText(/That's right!/);

        Object.defineProperty(document, 'visibilityState', {
            configurable: true,
            get: () => 'hidden',
        });
        fireEvent(document, new Event('visibilitychange'));

        await waitFor(() => {
            expect(api.resultsCalls.length).toEqual(1);
        });
        expect(api.resultsCalls[0].answers).toEqual([
            { answer: 'man', glossId: 10, lexicalEntryId: 1 },
        ]);
    });
});
