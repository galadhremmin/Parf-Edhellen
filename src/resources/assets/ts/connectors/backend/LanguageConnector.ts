import {
    LocalStorageLanguages,
} from '../../config';

import ExpiringCache, { IDataWithExpiration, TimeUnit } from '../../utilities/ExpiringCache';
import LazyLoader from '../../utilities/LazyLoader';
import BookApiConnector from './BookApiConnector';
import {
    ILanguageEntity,
    ILanguagesResponse,
} from './BookApiConnector._types';

export default class LanguageConnector {
    constructor(private _api: BookApiConnector = new BookApiConnector(),
        private _cache?: LazyLoader<ILanguagesResponse>) {
        if (_cache === undefined) {
            this._cache = ExpiringCache.withLocalStorage(this._load.bind(this),
                LocalStorageLanguages);
        }
    }

    /**
     * Gets all languages.
     */
    public async all() {
        const languages = await this._cache.get();
        return languages;
    }

    /**
     * Finds one, and only one, language that matches the specified `value`.
     * Returns `null` if no languages matches your query.
     * @param value
     * @param key (optional) the property on the language object that contains the expected `value`.
     * @param cmpFunc (optional) comparer; `===` by default.
     */
    public async find(value: any, key: keyof ILanguageEntity = 'id', cmpFunc = (a: any, b: any) => a === b) {
        const categorizedLanguages = await this.all();
        const categories = Object.keys(categorizedLanguages);

        for (const category of categories) {
            const languages = categorizedLanguages[category];
            const language = languages.find((l: any) => cmpFunc(l[key], value));

            if (typeof language === 'object') {
                return language;
            }
        }

        return null;
    }

    /**
     * Default language loader. Makes a request to the service API.
     */
    private async _load() {
        const languages = await this._api.languages();
        const cacheConfig: IDataWithExpiration<ILanguagesResponse> = {
            data: languages,
            lifetime: 1,
            unit: TimeUnit.Days,
        };

        return cacheConfig;
    }
}
