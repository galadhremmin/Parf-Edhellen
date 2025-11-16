import { ApplicationGlobalPrefix } from '@root/config';

import { isEmptyString } from './func/string-manipulation';
import LazyLoader, { type ILoader } from './LazyLoader';
import MemoryStorage from './MemoryStorage';
import { isNodeJs } from './func/node';

/**
 * Provides a cache for records of type `T`. The payload is optionally wrapped by type `R`.
 */
export default class Cache<T, R = T> extends LazyLoader<T, T> {
    public static withPersistentStorage<T, R = T>(loader: ILoader<T>, storageKey: string) {
        if (isNodeJs()) {
            return this.withMemoryStorage<T, R>(loader, storageKey);
        } else {
            return this.withLocalStorage<T, R>(loader, storageKey);
        }
    }
    public static withTransientStorage<T, R = T>(loader: ILoader<T>, storageKey: string) {
        if (isNodeJs()) {
            return this.withMemoryStorage<T, R>(loader, storageKey);
        } else {
            return this.withSessionStorage<T, R>(loader, storageKey);
        }
    }

    public static withLocalStorage<T, R = T>(loader: ILoader<T>, storageKey: string) {
        return new this<T, R>(loader, window.localStorage, storageKey);
    }

    public static withSessionStorage<T, R = T>(loader: ILoader<T>, storageKey: string) {
        return new this<T, R>(loader, window.sessionStorage, storageKey);
    }

    public static withMemoryStorage<T, R = T>(loader: ILoader<T>, storageKey: string) {
        return new this<T, R>(loader, new MemoryStorage(), storageKey);
    }

    private _storageKey: string;

    constructor(loader: ILoader<T>, private _store: Storage, storageKey: string) {
        super(loader);

        if (isEmptyString(storageKey)) {
            throw new Error(`You must specify a storage key.`);
        }

        const prefix = `${ApplicationGlobalPrefix}.`;
        if (storageKey.indexOf(prefix) !== 0) {
            storageKey = `${prefix}${storageKey}`;
        }

        this._storageKey = storageKey;
    }

    public set(value: T) {
        this.loadedData = value;
        this.saveInStore(value);
    }

    protected async load() {
        let value = this.loadFromStore();

        if (value === null) {
            value = await super.load();
            this.saveInStore(value);
        }

        return value;
    }

    /**
     * Wraps the specified payload with the wrapper `R`.
     * @param record the payload
     * @returns a wrapped record
     */
    protected wrap(record: T): R {
        return record as unknown as R;
    }

    /**
     * Unwraps the specified record and returns the intended original type `T`
     * @param record the record
     * @returns the original payload
     */
    protected unwrap(record: R): T {
        return record as unknown as T;
    }

    /**
     * Attempts to load the value from the store, and returns `null` if
     * the item does not exist (or is corrupt).
     */
     private loadFromStore(): T {
        const json = this._store.getItem(this._storageKey);
        if (json !== null) {
            try {
                const value = JSON.parse(json) as R;
                return this.unwrap(value);
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
    private saveInStore(value: T) {
        const json = JSON.stringify(this.wrap(value));
        this._store.setItem(this._storageKey, json);
    }
}
