import { expect } from 'chai';

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

    before(() => {
        testCache = new TestCache(() => Promise.resolve(TestObject), CacheKey);
    });

    beforeEach(() => {
        testCache.clear();
    });

    it('is not alive', () => {
        expect(testCache.alive).to.equal(false);
    });

    it('loads with loader', async () => {
        expect(await testCache.get()).to.equal(TestObject);
    });

    it('loads from store', async () => {
        expect(await testCache.get()).to.not.equal(TestObject);
        expect(await testCache.get()).to.deep.equal(TestObject);
    });

    it(`prepends application global prefix "${ApplicationGlobalPrefix}"`, async () => {
        await testCache.get();
        const value = testCache.underlyingStore.getItem(`${ApplicationGlobalPrefix}.${CacheKey}`);
        expect(value).to.deep.equal(JSON.stringify(TestObject));
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
