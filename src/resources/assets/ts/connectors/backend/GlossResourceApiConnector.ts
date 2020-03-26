import { DI, resolve } from '@root/di';
import ApiConnector from '../ApiConnector';
import IGlossResourceApi, {
    IGetGlossResponse,
    ISuggestionEntity,
    ISuggestRequest,
    ISuggestResponse,
} from './IGlossResourceApi';
import ILanguageApi from './ILanguageApi';

const LanguageParameterRegEx = /\blang:([\w\s]+)$/u;

export default class GlossResourceApiConnector implements IGlossResourceApi {
    constructor(private _api = resolve<ApiConnector>(DI.BackendApi),
        private _languageApi = resolve<ILanguageApi>(DI.LanguageApi)) {
    }

    public delete(glossId: number, replacementId: number) {
        return this._api.delete<void>(`gloss/${glossId}`, { replacementId });
    }

    public async gloss(glossId: number) {
        const response = await this._api.get<IGetGlossResponse>(`gloss/${glossId}`);
        return response.gloss;
    }

    public async suggest(args: ISuggestRequest) {
        if (args.parameterized) {
            await this._parameterizeSuggestions(args);
        }

        const response = await this._api.post<ISuggestResponse>(`gloss/suggest`, args);
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
