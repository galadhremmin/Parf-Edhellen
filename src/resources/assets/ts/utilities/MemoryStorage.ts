export default class MemoryStorage implements Storage {
    private _data: any = {};

    public get length() {
        return Object.keys(this._data).length;
    }

    public clear() {
        this._data = {};
    }

    public getItem(key: string) {
        return (this._data[key] as string) || null;
    }

    public key(index: number) {
        return Object.keys(this._data)[index] || null;
    }

    public removeItem(key: string) {
        if (this.getItem(key) !== null) {
            this._data[key] = null;
            delete this._data[key];
        }
    }

    public setItem(key: string, value: string) {
        this._data[key] = value;
    }
}
