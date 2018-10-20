/// <reference path="../@types/di.d.ts" />

import {
    inject,
    injectable 
} from 'inversify';
import 'reflect-metadata';

import ApiConnector from './api';
import { InjectSessionCacheFactory } from '../config';
import { Cache } from '../utilities/cache';

interface Language {
    id: number;
    name: string;
}

interface LanguageMap {
    [period: string]: Language[];
}

@injectable()
export default class LanguageConnector {
    private _cache: Cache<LanguageMap>;

    constructor(private _api: ApiConnector, 
        @inject(InjectSessionCacheFactory) cacheFactory: Newable<Cache<LanguageMap>>) {
        this._cache = new cacheFactory(() => this.load(), 'ed.languages');
    }

    async load() {
        return await this._api.get<LanguageMap>('book/languages');
    }

    async all() {
        const languages = await this._cache.get();
        return languages;
    }

    /**
     * Gets all languages.
     */
    async find(value: any, key = 'id', cmpFunc = (a: any, b: any) => a === b): Promise<Language> {
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
