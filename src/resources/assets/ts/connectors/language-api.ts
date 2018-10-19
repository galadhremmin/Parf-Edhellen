import EDAPI from './api';
import { SessionCache } from '../utilities/cache';

interface Language {
}

interface LanguageMap {
    [period: string]: Language[];
}

export class LanguageApi {
    _cache: SessionCache<LanguageMap>;

    constructor(private _api: EDAPI = EDAPI.default) {
        this._cache = new SessionCache(() => this.load(), 'ed.languages');
    }

    async load() {
        return await this._api.get<LanguageMap>('book/languages');
    }

    /**
     * Gets all languages.
     */
    async languages(id: number = undefined, key = 'id', cmpFunc = (a: number, b: number) => a === b): Promise<Language> {
        const languages = await this._cache.get();
        return this._filter(id, key, cmpFunc, languages);
    }

    private _filter<T>(id: T, key: string, cmpFunc: (a: T, b: T) => boolean, languages: LanguageMap) {
        if (id === undefined) {
            return languages;
        }

        const categories = Object.keys(languages);

        for (let i = categories.length - 1; i >= 0; i -= 1) {
            const subLanguages = languages[categories[i]];

            for (let j = subLanguages.length - 1; j >= 0; j -= 1) {
                if (cmpFunc((subLanguages[j] as any)[key] as T, id)) {
                    return subLanguages[j];
                }
            }
        }

        return undefined;
    }
}
