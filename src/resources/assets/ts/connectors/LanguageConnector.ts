import 'reflect-metadata';

import {
    inject,
    injectable,
} from 'inversify';

import {
    INewable,
} from '../_types/di';
import {
    InjectSessionCacheFactory,
} from '../config';
import Cache from '../utilities/Cache';

import ApiConnector from './ApiConnector';

interface ILanguage {
    id: number;
    name: string;
}

interface ILanguageMap {
    [period: string]: ILanguage[];
}

@injectable()
export default class LanguageConnector {
    private _cache: Cache<ILanguageMap>;

    constructor(private _api: ApiConnector,
                @inject(InjectSessionCacheFactory) cacheFactory: INewable<Cache<ILanguageMap>>) {
        this._cache = new cacheFactory(() => this.load(), 'ed.languages');
    }

    public async load() {
        return await this._api.get<ILanguageMap>('book/languages');
    }

    public async all() {
        const languages = await this._cache.get();
        return languages;
    }

    /**
     * Gets all languages.
     */
    public async find(value: any, key = 'id', cmpFunc = (a: any, b: any) => a === b): Promise<ILanguage> {
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
