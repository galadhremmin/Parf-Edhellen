import { assert, expect } from 'chai';
import { Cache } from './cache';

interface TestObject {
    prop: boolean;
}

const TestObject = <TestObject> { prop: true };
const CacheKey = 'cache.session.unittest';

describe('utilities/cache', () => {
    before(() => {
        this.sessionCache = new TestCache(() => Promise.resolve(TestObject), CacheKey);
    });

    it('is not alive', () => {
        expect((<TestCache> this.sessionCache).alive).to.equal(false);
    });

    it('loads with loader', async () => {
        expect(await (<TestCache> this.sessionCache).get()).to.equal(TestObject);
    });

    it('loads from store', async () => {
        const cache = <TestCache> this.sessionCache;

        cache.clear();
        expect(await cache.get()).to.not.equal(TestObject);
        expect(await cache.get()).to.deep.equal(TestObject);
    });
});

/**
 * `localStorage` and `sessionStorage` mock because they are not available in nodejs.
 */
class TestCache extends Cache<TestObject> {
    storageContainer: any = {};

    protected get storage() {
        return <any> {
            getItem: (key: string) => {
                return this.storageContainer[key] || null;
            },
            setItem: (key: string, value: any) => {
                this.storageContainer[key] = value;
            }
        };
    }
}
