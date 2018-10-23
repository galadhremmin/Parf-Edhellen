import {
    LocalStorageLanguages,
} from '../../config';

import ExpiringCache from '../../utilities/ExpiringCache';
import LazyLoader from '../../utilities/LazyLoader';
import BookApiConnector, {
    ILanguageEntity,
    ILanguagesResponse,
} from './BookApiConnector';

export {
    ILanguageEntity,
    ILanguagesResponse,
} from './BookApiConnector';

export default class LanguageConnector {
    constructor(private _api: BookApiConnector = new BookApiConnector(),
        private _cache?: LazyLoader<ILanguagesResponse>) {
        if (_cache === undefined) {
            _cache = ExpiringCache.withLocalStorage(this.load.bind(this),
                LocalStorageLanguages);
        } else {
            // Replace the cache's loader with the internal loader.
            _cache.loader = this.load.bind(this);
        }
    }

    public async load() {
        return await this._api.languages();
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
