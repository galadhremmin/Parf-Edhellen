import {
    beforeEach,
    describe,
    expect,
    jest,
    test,
} from '@jest/globals';

import { ApplicationGlobalPrefix } from '@root/config';
import BoundedCache from './BoundedCache';
import MemoryStorage from './MemoryStorage';

/** Instantiate directly with MemoryStorage to avoid the isNodeJs() branch. */
function makeCache<T>(namespace: string, maxEntries: number) {
    return new BoundedCache<T>(new MemoryStorage(), namespace, maxEntries);
}

describe('utilities/BoundedCache', () => {
    describe('get / set / delete', () => {
        let cache: BoundedCache<{ x: number }>;

        beforeEach(() => {
            cache = makeCache('test', 10);
        });

        test('get returns null for an absent key', () => {
            expect(cache.get('missing')).toBeNull();
        });

        test('set then get round-trips the value', () => {
            cache.set('a', { x: 1 });
            expect(cache.get('a')).toEqual({ x: 1 });
        });

        test('set overwrites an existing entry', () => {
            cache.set('a', { x: 1 });
            cache.set('a', { x: 2 });
            expect(cache.get('a')).toEqual({ x: 2 });
        });

        test('delete removes the entry', () => {
            cache.set('a', { x: 1 });
            cache.delete('a');
            expect(cache.get('a')).toBeNull();
        });

        test('delete on an absent key does not throw', () => {
            expect(() => cache.delete('never-set')).not.toThrow();
        });
    });

    describe('key namespacing', () => {
        test('keys are prefixed with ApplicationGlobalPrefix and namespace', () => {
            const store = new MemoryStorage();
            const cache = new BoundedCache<string>(store, 'mynamespace', 10);
            cache.set('42', 'hello');
            const raw = store.getItem(`${ApplicationGlobalPrefix}.mynamespace.42`);
            expect(raw).not.toBeNull();
        });

        test('two caches with different namespaces do not share entries', () => {
            const store = new MemoryStorage();
            const a = new BoundedCache<number>(store, 'ns-a', 10);
            const b = new BoundedCache<number>(store, 'ns-b', 10);
            a.set('1', 100);
            expect(b.get('1')).toBeNull();
        });
    });

    describe('eviction (cap enforcement)', () => {
        test('entries below the cap are all retained', () => {
            const cache = makeCache<number>('evict', 3);
            cache.set('1', 1);
            cache.set('2', 2);
            cache.set('3', 3);
            expect(cache.get('1')).toBe(1);
            expect(cache.get('2')).toBe(2);
            expect(cache.get('3')).toBe(3);
        });

        test('the oldest entry is evicted when the cap is exceeded', () => {
            const store = new MemoryStorage();
            const cache = new BoundedCache<number>(store, 'evict', 2);

            // Set entries with increasing timestamps by manipulating Date.now.
            const now = Date.now();
            let tick = 0;
            jest.spyOn(Date, 'now').mockImplementation(() => now + tick++);

            cache.set('oldest', 1); // saved = now + 0
            cache.set('middle', 2); // saved = now + 1
            cache.set('newest', 3); // saved = now + 2  → triggers reap, 'oldest' removed

            jest.restoreAllMocks();

            expect(cache.get('oldest')).toBeNull();
            expect(cache.get('middle')).toBe(2);
            expect(cache.get('newest')).toBe(3);
        });

        test('multiple excess entries are all evicted in one reap', () => {
            const store = new MemoryStorage();
            const cache = new BoundedCache<number>(store, 'evict', 2);

            let tick = 0;
            jest.spyOn(Date, 'now').mockImplementation(() => tick++);

            // Pre-load the store with 4 entries directly so they all exist before
            // the 5th set() triggers a single reap pass.
            for (let i = 1; i <= 4; i++) {
                cache.set(String(i), i);
            }
            // After 4 sets, reaper runs each time; after the last one only the
            // two newest survive.
            jest.restoreAllMocks();

            const survivors = [1, 2, 3, 4].filter(i => cache.get(String(i)) !== null);
            expect(survivors.length).toBe(2);
        });
    });
});
