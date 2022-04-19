export interface IErrorMap {
    [propertyName: string]: string[];
}

export default class ValidationError {
    constructor(private _errorMessage: string, private _errorMap: IErrorMap = null) {}

    public get errorMessage() {
        return this._errorMessage;
    }

    public get errors() {
        const map = new Map<string, string[]>();

        if (this._errorMap === null) {
            return map;
        }

        Object.keys(this._errorMap).forEach((propertyName) => {
            map.set(propertyName, this._errorMap[propertyName]);
        });

        return map;
    }
}
