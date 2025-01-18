import Cache from './Cache';
import { isNodeJs } from './func/node';
import { ILoader } from './LazyLoader';
import MemoryStorage from './MemoryStorage';

export default class ExpiringCache<T> extends Cache<T, IExpiringRecord<T>> {
    private _lifetime: number;
    private _unit: TimeUnit;

    public static persistentStore() {
        if (isNodeJs()) {
            return new MemoryStorage();
        }

        return window.localStorage;
    }

    public static transientStorage() {
        if (isNodeJs()) {
            return new MemoryStorage();
        }

        return window.sessionStorage;
    }

    constructor(loader: ILoader<T>, store: Storage, storageKey: string, lifetime = 1, unit = TimeUnit.Hours) {
        super(loader, store, storageKey);
        this._lifetime =  lifetime;
        this._unit = unit;
    }

    public get lifetime() {
        return this._lifetime;
    }

    public set lifetime(value: number) {
        this._lifetime = value;
    }

    public get unit() {
        return this._unit;
    }

    public set unit(unit: TimeUnit) {
        this._unit = unit;
    }

    protected wrap(payload: T) {
        const wrappedData: IExpiringRecord<T> = {
            d: payload,
            t: this._getTimeExpired(),
        };

        return wrappedData;
    }

    protected unwrap(record: IExpiringRecord<T>) {
        if (record === null) {
            return null;
        }

        const time = this._getTime();
        if (record.t <= time) {
            return null;
        }

        return record.d;
    }

    private _getTime() {
        return new Date().getTime();
    }

    private _getTimeExpired() {
        return this._getTime() + this._lifetime * this._unit;
    }
}

export interface IDataWithExpiration<T> {
    data: T;
    lifetime: number;
    unit: TimeUnit;
}

export enum TimeUnit {
    Milliseconds = 1,
    Seconds      = 1000,
    Minutes      = 60 * 1000,
    Hours        = 60 * 60 * 1000,
    Days         = 24 * 60 * 60 * 1000,
}

interface IExpiringRecord<T> {
    d: T;
    t: number;
}
