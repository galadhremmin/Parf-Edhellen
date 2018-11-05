import { Dispatch } from 'redux';
import { ThunkDispatch } from 'redux-thunk';

import BookApiConnector from '../../../connectors/backend/BookApiConnector';
import {
    IFindEntity,
    IGlossaryRequest,
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
import { ILoadGlossaryAction } from './SearchActions._types';

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
                        id: (r) => stringHash(`${(r.ok || '')}.${r.k}`),
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
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => IRootReducer) => {
            const searchResults = getState().searchResults;
            if (searchResults.length < 1) {
                return;
            }

            let selectedIndex = searchResults.findIndex((result) => result.selected) + direction;
            if (selectedIndex < 0) {
                selectedIndex = searchResults.length - 1;
            } else if (selectedIndex >= searchResults.length) {
                selectedIndex = 0;
            }

            const searchResult = searchResults[selectedIndex];
            const includeOld = getState().search.includeOld;
            const languageId = getState().search.languageId;

            const args = {
                includeOld,
                languageId,
                searchResult,
                updateBrowserHistory: true,
            };
            await this.glossary(args)(dispatch);
        };
    }

    public glossary(args: ILoadGlossaryAction) {
        return async (dispatch: ThunkDispatch<any, any, any>) => {
            const includeOld = args.includeOld || true;
            const languageId = args.languageId || 0;
            const word = args.searchResult.originalWord || //
                args.searchResult.word;

            let language: ILanguageEntity = null;
            let languageShortName: string = null;
            if (languageId !== 0) {
                language = await this._languages.find(languageId);
                languageShortName = language.shortName;
            }

            dispatch(this.selectSearchResult(args.searchResult));

            const request = {
                includeOld,
                inflections: true,
                languageId,
                word,
            };
            await this._loadGlossary(dispatch, request, languageShortName, args.updateBrowserHistory);
        };
    }

    public loadReference(word: string, languageShortName: string) {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => IRootReducer) => {
            const language = await this._languages.find(languageShortName, 'shortName');
            let languageId = 0;
            if (language !== null) {
                languageId = language.id;
            }

            const state = getState();
            await this._loadGlossary(dispatch, {
                includeOld: state.search.includeOld,
                languageId,
                word,
            }, languageShortName);
        };
    }

    public setGlossary(glossary: IGlossaryResponse) {
        return {
            glossary,
            type: Actions.ReceiveGlossary,
        };
    }

    private async _loadGlossary(dispatch: ThunkDispatch<any, any, any>, args: IGlossaryRequest,
        languageShortName: string = null, updateBrowserHistory: boolean = true) {
        const uriEncodedWord = encodeURIComponent(args.word);
        const capitalizedWord = capitalize(args.word);

        // Browser specific: build the browser's new title and its new address.
        const title = `${capitalizedWord} - Parf Edhellen`;
        const address = `/w/${uriEncodedWord}` + (languageShortName ? `/${languageShortName}` : '');

        // When navigating using the browser's back and forward buttons,
        // the state needn't be modified.
        if (updateBrowserHistory && window.history.pushState) {
            window.history.pushState(null, title, address);
        }

        // because most browsers doesn't change the document title when pushing state
        document.title = title;

        // Inform indirect listeners about the navigation
        const event = new CustomEvent('ednavigate', {
            detail: {
                address,
                languageId: args.languageId,
                word: args.word,
            },
        });
        window.dispatchEvent(event);

        const glossary = await this._api.glossary(args);
        dispatch(this.setGlossary(glossary));
    }
}
