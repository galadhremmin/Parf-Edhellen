export default class LazyLoader<T, L = T> {
    private _data: T;

    constructor(private _loader: ILoader<L>) {
        this._data = null;
    }

    /**
     * Gets whether the value is presently alive, i.e. successfully loaded.
     */
    public get alive(): boolean {
        return this.loadedData !== null;
    }

    /**
     * Clear the instance currently kept in memory.
     */
    public clear() {
        this.loadedData = null;
    }

    /**
     * Gets the loader.
     */
    public get loader() {
        return this._loader;
    }

    /**
     * Sets the loader.
     */
    public set loader(loader: ILoader<L>) {
        this._loader = loader;
        this.loadedData = null; // reset the loaded data since the loader changed.
    }

    /**
     * Gets the value from memory, or loads (if necessary) the value from the loader.
     */
    public async get(): Promise<T> {
        if (!this.alive) {
            this.loadedData = await this.load();
        }

        return this.loadedData;
    }

    /**
     * Triggers to loader and returns its value.
     */
    protected async load(): Promise<T> {
        try {
            const data = await this.loader.call(this) as L;
            return this.adapt(data);
        } catch (e) {
            throw new Error(String(e));
        }
    }

    /**
     * Adapts the recipient payload from `L` to `T`. Must be implemented when `L` != `L`.
     * @param data payload from `load()`
     */
    protected adapt(data: L): T {
        return data as unknown as T;
    }

    /**
     * Gets the loaded data.
     */
    protected get loadedData() {
        return this._data;
    }

    /**
     * Sets the loaded data.
     */
    protected set loadedData(value: T) {
        this._data = value;
    }
}

export type ILoader<T> = () => Promise<T>;
