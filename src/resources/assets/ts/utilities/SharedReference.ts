import { INewable } from '../_types';
import { ApplicationGlobalPrefix } from '../config';

declare var window: Window; // compatibility with browser.
declare var global: any; // compatibility with node.js.

export default class SharedReference<T> {
    static get container() {
        return typeof window === 'object' ? window : global;
    }

    private _name: string;
    constructor(private _constructor: INewable<T>,
        name: string = _constructor.name || null,
        prefix: string = ApplicationGlobalPrefix) {
        if (name === null) {
            throw new Error(`${_constructor.toString()} does not have a name.
                If a name cannot be inferred, make sure to specify one.`);
        }

        this._name = `${prefix}.${name}`;
    }

    get value(): T {
        const container = SharedReference.container;

        let instance = container[this._name];
        if (instance === undefined) {
            instance = container[this._name] = new this._constructor();
        }

        return instance;
    }
}
