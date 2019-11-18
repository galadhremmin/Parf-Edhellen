import {
    ILanguageEntity,
    ILanguagesResponse,
} from './IBookApi';

type LanguageComparator = (a: ILanguageEntity, b: ILanguageEntity) => boolean;

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
    find(value: any, key?: keyof ILanguageEntity, cmpFunc?: LanguageComparator): Promise<ILanguageEntity>;
}
