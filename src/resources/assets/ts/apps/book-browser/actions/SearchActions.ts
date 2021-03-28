import queryString from 'query-string';
import { Dispatch } from 'redux';
import { ThunkDispatch } from 'redux-thunk';

import IBookApi, {
    IFindEntity,
    IEntitiesRequestData,
    ILanguageEntity,
    IEntitiesRequest,
    IEntitiesResponse,
} from '@root/connectors/backend/IBookApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import {
    SearchResultGlossaryGroupId,
    SearchResultGroups,
} from '@root/config';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { DI, resolve } from '@root/di';
import { stringHashAll } from '@root/utilities/func/hashing';
import { mapArrayGroupBy } from '@root/utilities/func/mapper';
import { capitalize } from '@root/utilities/func/string-manipulation';
import { toSnakeCase } from '@root/utilities/func/snake-case';

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
    IExpandSearchResultAction,
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

            let results: Map<string, ISearchResult[]> = new Map();
            if (typeof args.word === 'string' && args.word.length > 0) {
                try {
                    const rawResults = await this._api.find(args);
                    // generate a unique ID for each result item. We need to use an counter since
                    // the keyword and the normalized keyword both may not be unique.
                    results = mapArrayGroupBy<IFindEntity, ISearchResult>({
                        groupId: 'g',
                        id: (v) => stringHashAll(v.k, v.nk, v.ok, v.g.toString(10)),
                        normalizedWord: 'nk',
                        originalWord: 'ok',
                        word: 'k',
                    }, rawResults, (v) => {
                        const groupId: keyof typeof SearchResultGroups = v.g.toString(10) as any;
                        if (! SearchResultGroups.hasOwnProperty(groupId)) {
                            return SearchResultGroups['0'];
                        }

                        return SearchResultGroups[groupId];
                    });
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
            const {
                resultIds,
                resultsById,
                selectedId,
            } = getState().searchResults;
            if (resultIds.length < 1) {
                return;
            }

            let selectedIndex = resultIds.indexOf(selectedId) + direction;
            if (selectedIndex < 0) {
                selectedIndex = resultIds.length - 1;
            } else if (selectedIndex >= resultIds.length) {
                selectedIndex = 0;
            }

            const searchResult = resultsById[resultIds[selectedIndex]];
            if (!searchResult) {
                // Do nothing if the search result doesn't exist.
                console.warn(`Trying to expand a non-existant search result for ${selectedIndex}.`);
                return;
            }
            const args = {
                searchResult,
                updateBrowserHistory: true,
            };

            await this.expandSearchResult(args)(dispatch, getState);
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
     * Loads entities asspcoated with the specified arguments, including (but not necessarily related to) the glossary.
     * @param args Search action arguments
     */
    public expandSearchResult(args: IExpandSearchResultAction) {
        const {
            searchResult,
            updateBrowserHistory,
        } = args;

        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const state = getState();

            let {
                glossGroupIds,
                includeOld,
                languageId,
                speechIds,
            } = state.search;

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

            const request: IEntitiesRequest = {
                data: {
                    glossGroupIds,
                    includeOld,
                    inflections: true,
                    languageId,
                    normalizedWord: args.searchResult.normalizedWord,
                    speechIds,
                    word,
                },
                groupId: searchResult.groupId,
            };

            const {
                address,
                title,
            } = this._prepareAddress(request);

            // When navigating using the browser's back and forward buttons,
            // the state needn't be modified.
            if (updateBrowserHistory && window.history.pushState) {
                const nextState: IBrowserHistoryState = {
                    glossary: true,
                    languageShortName,
                    groupId: searchResult.groupId,
                    normalizedWord: searchResult.normalizedWord,
                    word: searchResult.word,
                };
                window.history.pushState(nextState, title, address);
            }

            // because most browsers doesn't change the document title when pushing state
            document.title = title;

            this._globalEvents.fire(this._globalEvents.loadEntity, {
                detail: {
                    address,
                    groupId: searchResult.groupId,
                    languageId: language?.id,
                    word: searchResult.word,
                },
            });

            // DEPRECATED: Inform indirect listeners about the navigation. Kept for backwards compatibility.
            if (searchResult.groupId === SearchResultGlossaryGroupId) {
                this._globalEvents.fire(this._globalEvents.loadGlossary, {
                    detail: {
                        address,
                        languageId: args.languageId,
                        word,
                    },
                });
            }

            dispatch(this.selectSearchResult(args.searchResult));

            dispatch({
                groupId: args.searchResult.groupId,
                type: Actions.RequestEntities,
            });
            const entities = await this._api.entities(request);
            dispatch(this.setEntities(entities));
        }
    }

    /**
     * Reloads the glossary based on current state.
     */
    public reloadGlossary() {
        return async (dispatch: ThunkDispatch<any, any, any>, getState: () => RootReducer) => {
            const {
                entities,
                search,
                searchResults,
            } = getState();
            const {
                selectedId,
                resultsById,
            } = searchResults;

            // Do not attempt to reload an uninitiated glossary.
            if (entities.word.length < 1) {
                return;
            }

            // Attempt to find the selected search result, first by looking at the `selected` property,
            // and secondarily by comparing the values of the `word` property. Lastly, if the search
            // result does not exist, create a fake search result (id = 0) for the glossary.
            let searchResult: ISearchResult;
            if (selectedId !== null) {
                searchResult = resultsById[selectedId] || null;
            }
            if (searchResult === null) {
                searchResult = Object.values(resultsById).find((r) => r.word === entities.word) || null;
            }
            if (searchResult === null) {
                const word = entities.word;
                searchResult = {
                    id: 0,
                    groupId: SearchResultGlossaryGroupId,
                    normalizedWord: word,
                    originalWord: null,
                    word,
                };
            }

            await this.expandSearchResult({
                ...search,
                searchResult,
                updateBrowserHistory: true,
            })(dispatch, getState);
        };
    }

    /**
     * Loads the glossary for the reference link._expandSearchResult
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
            const args: IExpandSearchResultAction = {
                ...state.search,
                languageId,
                searchResult: {
                    id: 0,
                    groupId: SearchResultGlossaryGroupId,
                    normalizedWord,
                    originalWord: null as string,
                    word,
                },
                updateBrowserHistory,
            };

            this.expandSearchResult(args);
        };
    }

    /**
     * Sets the specified `glossary`.
     * @param glossary
     */
    public setEntities<T>(entities: IEntitiesResponse<T>) {
        return {
            ...entities,
            type: Actions.ReceiveEntities,
        };
    }

    private _prepareAddress(args: IEntitiesRequest, languageShortName: string = null) {
        const {
            groupId,
        } = args;

        const {
            normalizedWord,
            word,
        } = args.data;

        const uriEncodedWord = encodeURIComponent(normalizedWord);
        const capitalizedWord = capitalize(word);

        let title: string;
        let address: string;

        // DEPRECATED: special rule for glossary.
        if (groupId === SearchResultGlossaryGroupId) {
            // Browser specific: build the browser's new title and its new address.
            title = `${capitalizedWord} - Parf Edhellen`;
            address = `/w/${uriEncodedWord}` + (languageShortName ? `/${languageShortName}` : '');
        } else {
            const groupName = SearchResultGroups[groupId].toLocaleLowerCase();
            title = `${capitalizedWord} - Parf Edhellen`;
            address = `/e/${groupName}/${uriEncodedWord}` + (languageShortName ? `/${languageShortName}` : '');
        }

        // embellish the address with configuration values that are not supported by the native URL format
        const supportedSettings: (keyof typeof args.data)[] = [
            'glossGroupIds', 'includeOld', 'speechIds',
        ];

        const settings: { [key: string]: any } = {};
        let noOfSettings = 0;
        for (const setting of supportedSettings) {
            const value = args.data[setting];
            // tslint:disable-next-line: no-bitwise
            settings[toSnakeCase(setting)] = typeof value === 'boolean' ? ~~value : value;
            noOfSettings += 1;
        }

        if (noOfSettings > 0) {
            address += '?' + queryString.stringify(settings, {
                arrayFormat: 'bracket',
                skipEmptyString: true,
                skipNull: true,
            });
        }

        return {
            address,
            title,
        }
    }
}
