import { INewable } from '../_types';
import { ApplicationGlobalPrefix } from '../config';

declare const window: Window; // compatibility with browser.
declare const global: Record<string, unknown>; // compatibility with node.js.

export default class SharedReference<T> {
    /**
     * Gets the instance of `T` shared across modules.
     * @param type type `T`. Must have a constructor.
     */
    public static getInstance<T>(type: INewable<T>) {
        const instance = new this(type);
        return instance.value;
    }

    private static get container() {
        return (typeof window === 'object' ? window : global) as Record<string, unknown>;
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

        if (_type.shared === false) {
            throw new Error(`Type ${name} is configured to disallow shared references.`);
        }

        this._name = `${prefix}.${name}`;
    }

    /**
     * Gets the shared instance.
     */
    public get value() {
        const container = SharedReference.container;

        let instance = container[this._name] as T;
        if (instance === undefined) {
            instance = container[this._name] = new this._type();
        }

        return instance;
    }
}
