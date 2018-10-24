import Cache from './Cache';

export default class ExpiringCache<T> extends Cache<T, IDataWithExpiration<T>> {
    private _lifetime: number;

    protected adapt(data: IDataWithExpiration<T>): T {
        this._lifetime = this._convertToMilliseconds(data.lifetime, data.unit);
        return data.data;
    }

    protected loadFromStore(): T {
        const data = this._convertToExpiringRecord(super.loadFromStore());
        if (data === null) {
            return null;
        }

        const time = this._getTime();
        if (data.t <= time) {
            return null;
        }

        return data.d;
    }

    protected saveInStore(data: T) {
        const wrappedData: IExpiringRecord<T> = {
            d: data,
            t: this._getTimeExpired(),
        };

        super.saveInStore(wrappedData as any);
    }

    private _convertToMilliseconds(lifetime: number, unit: TimeUnit) {
        return lifetime * unit.valueOf();
    }

    private _convertToExpiringRecord(data: T) {
        return data as unknown as IExpiringRecord<T>;
    }

    private _getTime() {
        return new Date().getTime();
    }

    private _getTimeExpired() {
        return this._getTime() + this._lifetime;
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
