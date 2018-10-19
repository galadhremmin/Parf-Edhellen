export interface Loader<T> {
    (): Promise<T>;
}

export default class LazyLoader<T> {
    private _data: T;

    constructor(private _loader: Loader<T>) {
        this._data = null;
    }

    /**
     * Gets whether the value is presently alive, i.e. successfully loaded.
     */
    get alive(): boolean {
        return this._data !== null;
    }

    /**
     * Clear the instance currently kept in memory.
     */
    clear() {
        this._data = null;
    }

    /**
     * Gets the value from memory, or loads (if necessary) the value from the loader.
     */
    async get(): Promise<T> {
        if (!this.alive) {
            this._data = await this.load();
        }

        return this._data;
    }

    /**
     * Triggers to loader and returns its value.
     */
    protected async load(): Promise<T> {
        return this._loader();
    }
}
