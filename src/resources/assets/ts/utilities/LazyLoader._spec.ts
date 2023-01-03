import {
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';
import LazyLoader from './LazyLoader';

describe('utilities/LazyLoader', () => {
    let loader: LazyLoader<boolean>;

    beforeEach(() => {
        loader = new LazyLoader(() => Promise.resolve(true));
    });

    test('is not alive', () => {
        expect(loader.alive).toEqual(false);
    });

    test('loads', async () => {
        expect(await loader.get()).toEqual(true);
    });

    test('fails', (done) => {
        const error = 'error';
        const failingLoader = new LazyLoader(() => Promise.reject(error));

        failingLoader.get().then(
            () => done('an error was not thrown'),
            () => done());
    });

    test('is cleared', () => {
        loader.clear();
        expect(loader.alive).toEqual(false);
    });
});
