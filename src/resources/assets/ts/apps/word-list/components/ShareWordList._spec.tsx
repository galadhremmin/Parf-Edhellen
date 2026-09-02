import {
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';
import { fireEvent, render, screen, waitFor } from '@testing-library/react';

import type { IWordListDetail } from '@root/connectors/backend/IWordListApi';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';

import ShareWordList from './ShareWordList';

/** Every visibility change made through the panel, in order, across all resolved instances. */
const updates: { id: number, isPublic: boolean }[] = [];

class MockedWordListApi {
    public update(id: number, args: { isPublic: boolean }): Promise<void> {
        updates.push({ id, isPublic: args.isPublic });
        return Promise.resolve();
    }
}

const wordListOf = (isPublic: boolean): IWordListDetail => ({
    entries: [],
    id: 7,
    isPublic,
    name: 'Words of the day',
    url: 'https://example.com/word-lists/7/words_of_the_day',
} as IWordListDetail);

describe('apps/word-list/components/ShareWordList', () => {
    beforeEach(() => {
        updates.length = 0;
        setInstance(DI.WordListApi, MockedWordListApi);
    });

    test('states the visibility on the page rather than behind a dialog', () => {
        const { container } = render(<ShareWordList wordList={wordListOf(false)}
                                                    canEdit={true}
                                                    onVisibilityChange={() => {}} />);

        expect(container.querySelector('.ShareWordList')).toBeTruthy();
        expect(screen.getByText('Private')).toBeTruthy();
        expect(container.textContent).toContain('Only you can open the addresses below');

        // Both addresses are offered straight away — no click needed to reach them.
        const inputs = container.querySelectorAll<HTMLInputElement>('input[readonly]');
        expect(Array.from(inputs).map((input) => input.value)).toEqual([
            'https://example.com/word-lists/7/words_of_the_day',
            '[Words of the day](https://example.com/word-lists/7/words_of_the_day)',
        ]);
    });

    test('says who can read a public list', () => {
        const { container } = render(<ShareWordList wordList={wordListOf(true)}
                                                    canEdit={true}
                                                    onVisibilityChange={() => {}} />);

        expect(screen.getByText('Public')).toBeTruthy();
        expect(container.textContent).toContain('including people who were not given the address');
    });

    test('publishes the list and reports it back to the container', async () => {
        let changedTo: boolean = null;
        render(<ShareWordList wordList={wordListOf(false)}
                              canEdit={true}
                              onVisibilityChange={(isPublic) => { changedTo = isPublic; }} />);

        fireEvent.click(screen.getByText('Make public'));

        await waitFor(() => {
            expect(updates).toEqual([ { id: 7, isPublic: true } ]);
            expect(changedTo).toBe(true);
        });
    });

    test('offers no visibility control to somebody who does not own the list', () => {
        const { container } = render(<ShareWordList wordList={wordListOf(true)}
                                                    canEdit={false}
                                                    onVisibilityChange={() => {}} />);

        expect(container.querySelector('.ShareWordList--visibility')).toBeNull();
        expect(container.querySelectorAll('input[readonly]')).toHaveLength(2);
    });
});
