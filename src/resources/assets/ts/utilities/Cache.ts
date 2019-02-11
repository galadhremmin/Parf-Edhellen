import { ApplicationGlobalPrefix } from '@root/config';

import { isEmptyString } from './func/string-manipulation';
import LazyLoader, { ILoader } from './LazyLoader';

export default class Cache<T, L = T> extends LazyLoader<T, L> {
    public static withLocalStorage<T, L = T>(loader: ILoader<L>, storageKey: string) {
        return new this<T, L>(loader, window.localStorage, storageKey);
    }

    public static withSessionStorage<T, L = T>(loader: ILoader<L>, storageKey: string) {
        return new this<T, L>(loader, window.sessionStorage, storageKey);
    }

    private _storageKey: string;

    constructor(loader: ILoader<L>, private _store: Storage, storageKey: string) {
        super(loader);

        if (isEmptyString(storageKey)) {
            throw new Error(`You must specify a storage key.`);
        }

        const prefix = `${ApplicationGlobalPrefix}.`;
        if (! storageKey.startsWith(prefix)) {
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
     * Attempts to load the value from the store, and returns `null` if
     * the item does not exist (or is corrupt).
     */
    protected loadFromStore(): T {
        const json = this._store.getItem(this._storageKey);
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
    protected saveInStore(value: T) {
        const json = JSON.stringify(value);
        this._store.setItem(this._storageKey, json);
    }
}
