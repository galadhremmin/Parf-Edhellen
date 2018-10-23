export type ILoader<T> = () => Promise<T>;

export default class LazyLoader<T> {
    private _data: T;

    constructor(private _loader: ILoader<T>) {
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
    public set loader(loader: ILoader<T>) {
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
        return this.loader.call(this);
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
