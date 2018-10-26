import {
    LocalStorageLanguages,
} from '../../config';

import ExpiringCache, { IDataWithExpiration, TimeUnit } from '../../utilities/ExpiringCache';
import LazyLoader from '../../utilities/LazyLoader';
import BookApiConnector from './BookApiConnector';
import {
    ILanguageEntity,
    ILanguagesResponse,
} from './BookApiConnector.types';

export default class LanguageConnector {
    constructor(private _api: BookApiConnector = new BookApiConnector(),
        private _cache?: LazyLoader<ILanguagesResponse>) {
        if (_cache === undefined) {
            this._cache = ExpiringCache.withLocalStorage(this.load.bind(this),
                LocalStorageLanguages);
        }
    }

    public async load() {
        const languages = await this._api.languages();
        const cacheConfig: IDataWithExpiration<ILanguagesResponse> = {
            data: languages,
            lifetime: 1,
            unit: TimeUnit.Days,
        };

        return cacheConfig;
    }

    public async all() {
        const languages = await this._cache.get();
        return languages;
    }

    /**
     * Gets all languages.
     */
    public async find(value: any, key = 'id', cmpFunc = (a: any, b: any) => a === b): Promise<ILanguageEntity> {
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
}
