import LazyLoader, { ILoader } from './LazyLoader';

export default class Cache<T> extends LazyLoader<T> {
    public static withLocalStorage<T>(loader: ILoader<T>, storageKey: string) {
        return new this(loader, window.localStorage, storageKey);
    }

    public static withSessionStorage<T>(loader: ILoader<T>, storageKey: string) {
        return new this(loader, window.sessionStorage, storageKey);
    }

    constructor(loader: ILoader<T>, private _store: Storage, private _storageKey: string) {
        super(loader);
    }

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
    private _saveInStore(value: T) {
        const json = JSON.stringify(value);
        this._store.setItem(this._storageKey, json);
    }
}
