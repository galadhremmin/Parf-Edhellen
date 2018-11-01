import { Dispatch } from 'redux';
import { ThunkDispatch } from 'redux-thunk';

import BookApiConnector from '../../../connectors/backend/BookApiConnector';
import {
    IFindEntity,
    IGlossaryResponse,
    ILanguageEntity,
} from '../../../connectors/backend/BookApiConnector._types';
import LanguageConnector from '../../../connectors/backend/LanguageConnector';
import { stringHash } from '../../../utilities/func/hashing';
import { mapArray } from '../../../utilities/func/mapper';
import { capitalize } from '../../../utilities/func/string-manipulation';
import SharedReference from '../../../utilities/SharedReference';
import { IRootReducer } from '../reducers';
import { Actions } from '../reducers/constants';
import { ISearchAction } from '../reducers/SearchReducer._types';
import {
    ISearchResult,
    ISelectSearchResultAction,
    ISetSearchResultAction,
} from '../reducers/SearchResultsReducer._types';

export default class SearchActions {
    constructor(private _api: BookApiConnector = SharedReference.getInstance(BookApiConnector),
        private _languages: LanguageConnector = SharedReference.getInstance(LanguageConnector)) {
    }

    public search(args: ISearchAction) {
        return async (dispatch: Dispatch) => {
            dispatch({
                type: Actions.RequestSearchResults,
                ...args,
            });

            let results: ISearchResult[] = [];
            if (typeof args.word === 'string' && args.word.length > 0) {
                try {
                    const rawResults = await this._api.find(args);

                    results = mapArray<IFindEntity, ISearchResult>({
                        id: (o) => stringHash(`${(o.ok || '')}.${o.k}`),
                        normalizedWord: 'nk',
                        originalWord: 'ok',
                        word: 'k',
                    }, rawResults);
                } catch (e) {
                    console.warn(e);
                }
            }

            dispatch(this.setSearchResults(results));
        };
    }

    public setSearchResults(searchResults: ISetSearchResultAction) {
        return {
            searchResults,
            type: Actions.ReceiveSearchResults,
        };
    }

    public selectSearchResult(searchResult: ISelectSearchResultAction) {
        return {
            id: searchResult.id,
            type: Actions.SelectSearchResult,
        };
    }

    public selectNextResult(direction: number) {
        return {
            direction,
            type: Actions.NextSearchResult,
        };
    }

    public glossary(searchResult: ISearchResult, updateBrowserHistory: boolean = true) {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => IRootReducer) => {
            const uriEncodedWord = encodeURIComponent(searchResult.normalizedWord || searchResult.word);
            const capitalizedWord = capitalize(searchResult.word);

            const includeOld = getState().search.includeOld;
            const languageId = getState().search.languageId;

            let language: ILanguageEntity;
            if (languageId !== 0) {
                language = await this._languages.find(languageId);
            }

            // Browser specific: build the browser's new title and its new address.
            const title = `${capitalizedWord} - Parf Edhellen`;
            const address = `/w/${uriEncodedWord}` + (language ? `/${language.shortName}` : '');

            // When navigating using the browser's back and forward buttons,
            // the state needn't be modified.
            if (updateBrowserHistory && window.history.pushState) {
                window.history.pushState(null, title, address);
            }

            // because most browsers doesn't change the document title when pushing state
            document.title = title;

            // Inform indirect listeners about the navigation
            const event = new CustomEvent('ednavigate', { detail: { address, word: searchResult.word, language } });
            window.dispatchEvent(event);

            dispatch(this.selectSearchResult(searchResult));

            const glossary = await this._api.glossary({
                includeOld,
                inflections: true,
                languageId,
                word: searchResult.word,
            });
            dispatch(this.setGlossary(glossary));

            // Find elements which is requested to be deleted upon receiving the navigation commmand
            /* TODO - weird location for this logic. Misplaced.
            const elementsToDelete = document.querySelectorAll('.ed-remove-when-navigating');
            if (elementsToDelete.length > 0) {
                for (const element of elementsToDelete) {
                    element.parentNode.removeChild(element);
                }
            }
            */
        };
    }

    public setGlossary(glossary: IGlossaryResponse) {
        return {
            glossary,
            type: Actions.ReceiveGlossary,
        };
    }
}
