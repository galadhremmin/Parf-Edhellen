import LazyLoader, { ILoader } from './lazy-loader';

export default abstract class Cache<T> extends LazyLoader<T> {
    constructor(loader: ILoader<T>, private _storageKey: string) {
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
