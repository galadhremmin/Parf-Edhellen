import { Dispatch } from 'redux';
import { ThunkDispatch } from 'redux-thunk';

import IBookApi, {
    IFindEntity,
    IGlossaryRequest,
    IGlossaryResponse,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { DI, resolve } from '@root/di';
import { stringHash } from '@root/utilities/func/hashing';
import { mapArray } from '@root/utilities/func/mapper';
import { capitalize } from '@root/utilities/func/string-manipulation';

import { RootReducer } from '../reducers';
import { ISearchAction } from '../reducers/SearchReducer._types';
import Actions from './Actions';

import {
    ISearchResult,
    ISelectSearchResultAction,
    ISetSearchResultAction,
} from '../reducers/SearchResultsReducer._types';
import {
    IBrowserHistoryState,
    ILoadGlossaryAction,
} from './SearchActions._types';

export default class SearchActions {
    constructor(private _api: IBookApi = resolve(DI.BookApi),
        private _languages: ILanguageApi = resolve(DI.LanguageApi),
        private _globalEvents = new GlobalEventConnector()) {
    }

    /**
     * Looks for keywords for the specified search query.
     * @param args
     */
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

    /**
     * Applies the specified array of search results.
     * @param searchResults
     */
    public setSearchResults(searchResults: ISetSearchResultAction) {
        return {
            searchResults,
            type: Actions.ReceiveSearchResults,
        };
    }

    /**
     * Selects the specified search result.
     * @param searchResult
     */
    public selectSearchResult(searchResult: ISelectSearchResultAction) {
        return {
            id: searchResult.id,
            type: Actions.SelectSearchResult,
        };
    }

    /**
     * Moves to the next (or previous) search result based on the specified `direction`;
     * +1 moves forward, -1 moves backwards.
     * @param direction +1 down, -1 up.
     */
    public selectNextResult(direction: number) {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
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

            const args = {
                searchResult,
                updateBrowserHistory: true,
            };
            await this.glossary(args)(dispatch, getState);
        };
    }

    /**
     * Picks amongst current search results based on the specified word.
     * @param word word
     */
    public selectSearchResultByWord(word: string) {
        return (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const results = getState().searchResults;
            if (! Array.isArray(results) || results.length < 1) {
                return;
            }

            const result = results.find((s) => s.word === word);
            if (result) {
                dispatch(this.selectSearchResult(result));
            }
        };
    }

    /**
     * Loads the glossary for the specified search result.
     * @param args
     */
    public glossary(args: ILoadGlossaryAction) {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const state = getState();

            let glossGroupIds = state.search.glossGroupIds;
            let includeOld    = state.search.includeOld;
            let languageId    = state.search.languageId;
            let speechIds     = state.search.speechIds;

            if (args.glossGroupIds !== undefined) {
                glossGroupIds = args.glossGroupIds;
            }

            if (args.includeOld !== undefined) {
                includeOld = args.includeOld;
            }

            if (args.languageId !== undefined) {
                languageId = args.languageId;
            }

            if (args.speechIds !== undefined) {
                speechIds = args.speechIds;
            }

            const word = args.searchResult.originalWord || //
                args.searchResult.word;

            let language: ILanguageEntity = null;
            let languageShortName: string = null;
            if (languageId !== 0) {
                language = await this._languages.find(languageId, 'id');
                languageShortName = language.shortName;
            }

            dispatch(this.selectSearchResult(args.searchResult));

            const request = {
                glossGroupIds,
                includeOld,
                inflections: true,
                languageId,
                normalizedWord: args.searchResult.normalizedWord,
                speechIds,
                word,
            };
            await this._loadGlossary(dispatch, request, languageShortName, args.updateBrowserHistory);
        };
    }

    /**
     * Reloads the glossary based on current state.
     */
    public reloadGlossary() {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const { glossary, search, searchResults } = getState();

            // Do not attempt to reload an uninitiated glossary.
            if (glossary.word.length < 1) {
                return;
            }

            // Attempt to find the selected search result, first by looking at the `selected` property,
            // and secondarily by comparing the values of the `word` property. Lastly, if the search
            // result does not exist, create a fake search result (id = 0) for the glossary.
            let searchResult = searchResults.find((r) => r.selected) || null;
            if (searchResult === null) {
                searchResult = searchResults.find((r) => r.word === glossary.word) || null;
            }
            if (searchResult === null) {
                const word = glossary.word;
                searchResult = {
                    id: 0,
                    normalizedWord: word,
                    originalWord: null,
                    selected: true,
                    word,
                };
            }

            await this.glossary({
                ...search,
                searchResult,
                updateBrowserHistory: true,
            })(dispatch, getState);
        };
    }

    /**
     * Loads the glossary for the reference link.
     * @param word
     * @param languageShortName
     * @param updateBrowserHistory (optional) whether to invoke pushState.
     */
    public loadReference(word: string, normalizedWord: string, languageShortName: string, updateBrowserHistory = true) {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const language = await this._languages.find(languageShortName, 'shortName');
            let languageId = 0;
            if (language !== null) {
                languageId = language.id;
            }

            const state = getState();
            const args = {
                includeOld: state.search.includeOld,
                languageId,
                normalizedWord,
                word,
            };
            await this._loadGlossary(dispatch, args, languageShortName, updateBrowserHistory);
        };
    }

    /**
     * Sets the specified `glossary`.
     * @param glossary
     */
    public setGlossary(glossary: IGlossaryResponse) {
        return {
            glossary,
            type: Actions.ReceiveGlossary,
        };
    }

    private async _loadGlossary(dispatch: ThunkDispatch<any, any, any>, args: IGlossaryRequest,
        languageShortName: string = null, updateBrowserHistory: boolean = true) {
        const uriEncodedWord = encodeURIComponent(args.normalizedWord);
        const capitalizedWord = capitalize(args.word);

        // Browser specific: build the browser's new title and its new address.
        const title = `${capitalizedWord} - Parf Edhellen`;
        const address = `/w/${uriEncodedWord}` + (languageShortName ? `/${languageShortName}` : '');

        // When navigating using the browser's back and forward buttons,
        // the state needn't be modified.
        if (updateBrowserHistory && window.history.pushState) {
            const state: IBrowserHistoryState = {
                glossary: true,
                languageShortName,
                normalizedWord: args.normalizedWord,
                word: args.word,
            };
            window.history.pushState(state, title, address);
        }

        // because most browsers doesn't change the document title when pushing state
        document.title = title;

        // Inform indirect listeners about the navigation
        this._globalEvents.fire(this._globalEvents.loadGlossary, {
            detail: {
                address,
                languageId: args.languageId,
                word: args.word,
            },
        });

        const glossary = await this._api.glossary(args);
        dispatch(this.setGlossary(glossary));
    }
}
