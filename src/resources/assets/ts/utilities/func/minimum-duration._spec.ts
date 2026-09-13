import {
    describe,
    expect,
    test,
} from '@jest/globals';

import withMinimumDuration from './minimum-duration';

describe('utilities/func/minimum-duration', () => {
    test('holds a fast result until the floor has passed', async () => {
        const startedAt = Date.now();

        const actual = await withMinimumDuration(Promise.resolve('estel'), 120);

        expect(actual).toEqual('estel');
        // Timers are not exact; allow a little slack under a loaded test runner.
        expect(Date.now() - startedAt).toBeGreaterThanOrEqual(110);
    });

    test('does not delay work that already takes longer than the floor', async () => {
        const startedAt = Date.now();
        const slowWork = new Promise((resolve) => setTimeout(() => resolve('estel'), 150));

        const actual = await withMinimumDuration(slowWork, 50);

        expect(actual).toEqual('estel');
        // Had the delay run after the work rather than alongside it, this would
        // be at least 200ms.
        expect(Date.now() - startedAt).toBeLessThan(190);
    });

    test('surfaces a failure without waiting out the floor', async () => {
        const startedAt = Date.now();
        const failure = Promise.reject(new Error('no such entry'));

        await expect(withMinimumDuration(failure, 400)).rejects.toThrow('no such entry');
        expect(Date.now() - startedAt).toBeLessThan(300);
    });

    test('resolves the value the work produced, not the timer', async () => {
        const work = Promise.resolve({ word: 'estel', glosses: [ 'hope' ] });

        const actual = await withMinimumDuration(work, 10);

        expect(actual).toEqual({ word: 'estel', glosses: [ 'hope' ] });
    });
});
