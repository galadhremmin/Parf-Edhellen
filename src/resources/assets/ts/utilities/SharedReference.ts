import { INewable } from '../_types';
import { ApplicationGlobalPrefix } from '../config';

declare var window: Window; // compatibility with browser.
declare var global: any; // compatibility with node.js.

export default class SharedReference<T> {
    static get container() {
        return typeof window === 'object' ? window : global;
    }

    private _name: string;

    /**
     * Creates a shared reference for the specified type `_type`.
     * @param _type type constructor.
     * @param name (optional) name used by the global key value instance store.
     * @param prefix (optional) key prefix for the instance store.
     */
    constructor(private _type: INewable<T>,
        name: string = _type.name || null,
        prefix: string = ApplicationGlobalPrefix) {
        if (name === null) {
            throw new Error(`${_type.toString()} does not have a name.
                If a name cannot be inferred, make sure to specify one.`);
        }

        this._name = `${prefix}.${name}`;
    }

    /**
     * Gets the shared instance.
     */
    get value() {
        const container = SharedReference.container;

        let instance: T = container[this._name];
        if (instance === undefined) {
            instance = container[this._name] = new this._type();
        }

        return instance;
    }
}
