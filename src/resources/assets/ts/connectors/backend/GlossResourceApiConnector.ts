import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type ILexicalEntryResourceApi from './IGlossResourceApi'; 
import type {
    IGetLexicalEntryResponse,
    ISuggestionEntity,
    ISuggestRequest,
    ISuggestResponse,
} from './IGlossResourceApi';

const LanguageParameterRegEx = /\blang:([\w\s]+)$/u;

export default class GlossResourceApiConnector implements ILexicalEntryResourceApi {
    constructor(private _api = resolve(DI.BackendApi),
        private _languageApi = resolve(DI.LanguageApi)) {
    }

    public delete(lexicalEntryId: number, replacementId: number) {
        return this._api.delete<void>(`lexical-entry/${lexicalEntryId}`, { replacementId });
    }

    public async lexicalEntry(lexicalEntryId: number) {
        const response = await this._api.get<IGetLexicalEntryResponse>(`lexical-entry/${lexicalEntryId}`);
        return response.lexicalEntry;
    }

    public async suggest(args: ISuggestRequest) {
        if (args.parameterized) {
            await this._parameterizeSuggestions(args);
        }

        const response = await this._api.post<ISuggestResponse>(`lexical-entry/suggest`, args);
        const map = new Map<string, ISuggestionEntity[]>();

        Object.keys(response).forEach((word) => {
            map.set(word, response[word]);
        });

        return map;
    }

    private async _parameterizeSuggestions(args: ISuggestRequest) {
        for (let i = 0; i < args.words.length; i += 1) {
            const word = args.words[i];
            const m = LanguageParameterRegEx.exec(word);
            if (m) {
                const languageName = m[1];
                const language = await this._languageApi.find(languageName, 'name',
                    (candidate, value) => candidate.toLocaleLowerCase().startsWith(value.toLocaleLowerCase()));

                if (language) {
                    args.languageId = language.id;
                }
                args.words[i] = word.replace(m[0], '').trim();
            }
        }
    }
}
