import { expect } from 'chai';
import Cache from './Cache';

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

    it('is not alive', () => {
        expect(testCache.alive).to.equal(false);
    });

    it('loads with loader', async () => {
        expect(await testCache.get()).to.equal(TestObject);
    });

    it('loads from store', async () => {
        testCache.clear();
        expect(await testCache.get()).to.not.equal(TestObject);
        expect(await testCache.get()).to.deep.equal(TestObject);
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
