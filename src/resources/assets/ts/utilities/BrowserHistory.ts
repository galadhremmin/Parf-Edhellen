import { DI, resolve } from '@root/di';
import {
    IHistory,
    UrlComponents,
} from './BrowserHistory._types';

export default class BrowserHistory {
    static get default() {
        return resolve<BrowserHistory>(DI.BrowserHistory);
    }

    constructor(private _history: IHistory = window.history) {
    }

    public push<TData>(urlComponents: UrlComponents, title: string = null, data: TData | null = null) {
        if (! this._assertOkay()) {
            return;
        }

        const url = this._createUrl(urlComponents);
        if (url !== null) {
            this._history.pushState(data, title, url);
        }
    }

    public redirect(urlComponents: UrlComponents) {
        const url = this._createUrl(urlComponents);
        if (url !== null) {
            window.location.href = url;
        }
    }

    private _assertOkay() {
        const history = this._history;
        return history && typeof history.pushState === 'function';
    }

    private _createUrl(urlComponents: UrlComponents) {
        if (typeof urlComponents === 'string') {
            return urlComponents;
        }

        const url = [];
        if (typeof urlComponents.path === 'string') {
            url.push(urlComponents.path);
        }

        if (typeof urlComponents.queryString === 'object') {
            const keys = Object.keys(urlComponents.queryString);

            if (keys.length > 0) {
                const queryString = [];
                for (const key of keys) {
                    const value = urlComponents.queryString[key];
                    queryString.push(`${key}=${encodeURIComponent(value)}`);
                }

                url.push(`?${queryString.join('&')}`);
            }
        }

        return url.length > 0
            ? url.join('')
            : null;
    }
}
