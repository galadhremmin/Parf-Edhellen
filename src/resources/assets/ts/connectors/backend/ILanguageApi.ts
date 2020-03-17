import {
    ILanguageEntity,
    ILanguagesResponse,
} from './IBookApi';

export type LanguageComparator<TKey> = (a: TKey, b: TKey) => boolean;

export default interface ILanguageApi {
    /**
     * Gets all languages.
     */
    all(): Promise<ILanguagesResponse>;

    /**
     * Finds one, and only one, language that matches the specified `value`.
     * Returns `null` if no languages matches your query.
     * @param value
     * @param key (optional) the property on the language object that contains the expected `value`.
     * @param cmpFunc (optional) comparer; `===` by default.
     */
    find<TKey extends keyof ILanguageEntity>(value: ILanguageEntity[TKey], key: TKey, cmpFunc?: LanguageComparator<ILanguageEntity[TKey]>): Promise<ILanguageEntity>;
}
