import LazyLoader, { Loader } from './lazy-loader';

interface MiddlewareResponse<T> {
    key?: string;
    value?: T;
    stop?: boolean;
}

export interface CacheMiddleware<T> {
    before?(key: string): MiddlewareResponse<T> | void;
    after?(value: T): MiddlewareResponse<T> | void;
}

export abstract class Cache<T> extends LazyLoader<T> {
    constructor(loader: Loader<T>, private _storageKey: string) {
        super(loader);
    }

    protected abstract get storage(): Storage;

    protected async load() {
        let value = this._loadFromStore();
        
        if (value === null) {
            value = await super.load();
            this._saveInStore(value);
        }

        return value;
    }

    /**
     * Attempts to load the value from the store, and returns `null` if 
     * the item does not exist (or is corrupt).
     */
    private _loadFromStore(): T {
        const json = this.storage.getItem(this._storageKey);
        if (json !== null) {
            try {
                const value = JSON.parse(json);
                return value;
            } catch (e) {
                // deliberate suppression
            }
        }

        return null;
    }

    /**
     * Stores the specified value payload in the store.
     * @param value the payload
     */
    private _saveInStore(value: T) {
        const json = JSON.stringify(value);
        this.storage.setItem(this._storageKey, json);
    }
}

/**
 * Cache using `sessionStorage`.
 */
export class SessionCache<T> extends Cache<T> {
    protected get storage() {
        return window.sessionStorage;
    }
}

/**
 * Cache using `localStorage`.
 */
export class LocalCache<T> extends Cache<T> {
    protected get storage() {
        return window.localStorage;
    }
}
