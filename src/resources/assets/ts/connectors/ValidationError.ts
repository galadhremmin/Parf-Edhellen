export default class ValidationError {
    constructor(private _errorMessage: string, private _errorMap: Record<string, string[]> = null) {}

    public get errorMessage() {
        return this._errorMessage;
    }

    public get errors() {
        if (this._errorMap === null) {
            return {};
        }

        return this._errorMap;
    }

    public get keys() {
        return Object.keys(this.errors);
    }

    public get size() {
        return this.keys.length;
    }
}
