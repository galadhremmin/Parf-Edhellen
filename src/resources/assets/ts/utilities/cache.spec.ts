import { expect } from 'chai';
import Cache from './cache';

interface ITestObjectSpec {
    prop: boolean;
}

describe('utilities/cache', () => {
    const TestObject: ITestObjectSpec = { prop: true };
    const CacheKey = 'cache.session.unittest';

    let sessionCache: TestCache<ITestObjectSpec>;

    before(() => {
        sessionCache = new TestCache(() => Promise.resolve(TestObject), CacheKey);
    });

    it('is not alive', () => {
        expect(sessionCache.alive).to.equal(false);
    });

    it('loads with loader', async () => {
        expect(await sessionCache.get()).to.equal(TestObject);
    });

    it('loads from store', async () => {
        sessionCache.clear();
        expect(await sessionCache.get()).to.not.equal(TestObject);
        expect(await sessionCache.get()).to.deep.equal(TestObject);
    });
});

/**
 * `localStorage` and `sessionStorage` mock because they are not available in nodejs.
 */
export class TestCache<T> extends Cache<T> {
    private _storageContainer: any = {};

    protected get storage() {
        return {
            getItem: (key: string) => {
                return this._storageContainer[key] || null;
            },
            setItem: (key: string, value: any) => {
                this._storageContainer[key] = value;
            },
        } as any;
    }
}
