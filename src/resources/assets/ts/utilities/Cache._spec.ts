import {
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';

import { ApplicationGlobalPrefix } from '@root/config';
import Cache from './Cache';
import MemoryStorage from './MemoryStorage';

interface ITestObjectSpec {
    prop: boolean;
}

describe('utilities/Cache', () => {
    const TestObject: ITestObjectSpec = { prop: true };
    const CacheKey = 'cache.session.unittest';

    let testCache: TestCache<ITestObjectSpec>;

    beforeEach(() => {
        testCache = new TestCache(() => Promise.resolve(TestObject), CacheKey);
    });

    test('is not alive', () => {
        expect(testCache.alive).toEqual(false);
    });

    test('loads with loader', async () => {
        expect(await testCache.get()).toEqual(TestObject);
    });

    test('loads from store', async () => {
        expect(await testCache.get()).toEqual(TestObject);
    });

    test(`prepends application global prefix "${ApplicationGlobalPrefix}"`, async () => {
        await testCache.get();
        const value = testCache.underlyingStore.getItem(`${ApplicationGlobalPrefix}.${CacheKey}`);
        expect(value).toEqual(JSON.stringify(TestObject));
    });
});

/**
 * `localStorage` and `sessionStorage` mock because they are not available in nodejs.
 */
export class TestCache<T> extends Cache<T> {
    public underlyingStore: MemoryStorage;

    constructor(loader: any, key: string) {
        const store = new MemoryStorage();
        super(loader, store, key);

        this.underlyingStore = store;
    }
}
