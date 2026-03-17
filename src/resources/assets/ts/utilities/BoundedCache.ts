import { ApplicationGlobalPrefix } from '@root/config';
import { isNodeJs } from './func/node';
import MemoryStorage from './MemoryStorage';

/**
 * Internal storage envelope. `d` = data, `s` = saved (Date.now() ms).
 * The `s` field is what the reaper sorts on to evict the oldest entries.
 */
interface IBoundedRecord<T> {
    d: T;
    s: number;
}

/**
 * A namespaced key-value store backed by a `Storage` object (localStorage,
 * sessionStorage, or in-memory) that caps the total number of entries.
 *
 * When the cap is exceeded on a `set()`, the oldest entries (by last-saved
 * timestamp) are evicted automatically.
 *
 * Keys are prefixed with `<ApplicationGlobalPrefix>.<namespace>.` so entries
 * from different `BoundedCache` instances never collide.
 *
 * Unlike `Cache` / `ExpiringCache` (which manage a single value per instance),
 * `BoundedCache` manages many values under one namespace, each identified by
 * a caller-supplied string id.
 *
 * @example
 * ```ts
 * const cache = BoundedCache.withLocalStorage<IDraft>('crossword.draft', 60);
 * cache.set('123', { cells: { '0:0': 'A' }, seconds: 42 });
 * const draft = cache.get('123'); // → { cells: ..., seconds: 42 } | null
 * cache.delete('123');
 * ```
 */
export default class BoundedCache<T> {
    /** Convenience factory: localStorage on the browser, MemoryStorage in Node. */
    public static withLocalStorage<T>(namespace: string, maxEntries: number): BoundedCache<T> {
        if (isNodeJs()) {
            return new BoundedCache<T>(new MemoryStorage(), namespace, maxEntries);
        }
        return new BoundedCache<T>(window.localStorage, namespace, maxEntries);
    }

    private readonly _prefix: string;

    constructor(
        private readonly _store: Storage,
        namespace: string,
        private readonly _maxEntries: number,
    ) {
        this._prefix = `${ApplicationGlobalPrefix}.${namespace}.`;
    }

    /**
     * Returns the stored value for `id`, or `null` if absent / unreadable.
     */
    get(id: string): T | null {
        try {
            const raw = this._store.getItem(this._prefix + id);
            if (!raw) return null;
            return (JSON.parse(raw) as IBoundedRecord<T>).d;
        } catch (e) {
            // deliberate suppression — storage unavailable or corrupt entry
            return null;
        }
    }

    /**
     * Saves `value` under `id` and reaps the oldest entries if the total
     * count now exceeds the cap.
     */
    set(id: string, value: T): void {
        try {
            const record: IBoundedRecord<T> = { d: value, s: Date.now() };
            this._store.setItem(this._prefix + id, JSON.stringify(record));
            this._reap();
        } catch (e) {
            // deliberate suppression — storage unavailable or quota exceeded
        }
    }

    /**
     * Removes the entry for `id`. No-ops silently if absent.
     */
    delete(id: string): void {
        try {
            this._store.removeItem(this._prefix + id);
        } catch (e) {
            // deliberate suppression — storage unavailable
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private _reap(): void {
        try {
            const entries: { key: string; s: number }[] = [];

            for (let i = 0; i < this._store.length; i++) {
                const key = this._store.key(i);
                if (!key || !key.startsWith(this._prefix)) continue;

                let s = 0;
                try {
                    const raw = this._store.getItem(key);
                    if (raw) {
                        s = (JSON.parse(raw) as IBoundedRecord<T>).s ?? 0;
                    }
                } catch (e) {
                    // deliberate suppression — corrupt entry, treat as oldest
                }

                entries.push({ key, s });
            }

            if (entries.length <= this._maxEntries) return;

            entries.sort((a, b) => a.s - b.s);
            for (const { key } of entries.slice(0, entries.length - this._maxEntries)) {
                this._store.removeItem(key);
            }
        } catch (e) {
            // deliberate suppression — storage unavailable
        }
    }
}
