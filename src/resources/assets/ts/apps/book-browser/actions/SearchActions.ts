import type { Dispatch } from 'redux';
import type { ThunkDispatch } from 'redux-thunk';

import { SearchResultGlossaryGroupId } from '@root/config';
import type {
    IEntitiesRequest,
    IEntitiesResponse,
    IFindEntity,
    IGlossaryResponse,
    ILanguageEntity,
    ISearchGroups,
} from '@root/connectors/backend/IBookApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { mapArrayGroupBy } from '@root/utilities/func/mapper';
import { toSnakeCase } from '@root/utilities/func/snake-case';
import { capitalize } from '@root/utilities/func/string-manipulation';

import type { RootReducer } from '../reducers';
import type { ISearchAction } from '../reducers/SearchReducer._types';
import Actions from './Actions';

import { buildQueryString } from '@root/utilities/func/query-string';
import type {
    ISearchResult,
    ISelectSearchResultAction,
    ISetSearchResultAction,
} from '../reducers/SearchResultsReducer._types';
import type {
    IBrowserHistoryState,
    IExpandSearchResultAction,
} from './SearchActions._types';

/**
 * Whether the glossary has taken over the address bar at least once in this document.
 *
 * Back navigation onto a state the glossary did not create — typically the very first entry, which the
 * server rendered and which therefore carries no history state at all — can then be answered with a real
 * page load. Without it the address would change back while the glossary kept showing the entry the
 * customer had just navigated away from.
 */
let addressOwnedByGlossary = false;

export function hasGlossaryChangedAddress() {
    return addressOwnedByGlossary;
}

export default class SearchActions {
    constructor(private _api = resolve(DI.BookApi),
        private _languages = resolve(DI.LanguageApi),
        private _globalEvents = resolve(DI.GlobalEvents)) {
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

            let keywords: Record<string, ISearchResult[]> = {};
            let searchGroups: ISearchGroups = {};
            if (typeof args.word === 'string' && args.word.length > 0) {
                try {
                    const rawResults = await this._api.find(args);

                    // generate a unique ID for each result item. We need to use a counter since
                    // the keyword and the normalized keyword both may not be unique, and a hash of
                    // their contents isn't collision-free either (32-bit hash space).
                    let nextId = 0;
                    keywords = mapArrayGroupBy<IFindEntity, ISearchResult>({
                        groupId: 'g',
                        id: () => nextId++,
                        normalizedWord: 'nk',
                        originalWord: 'ok',
                        word: 'k',
                    }, rawResults.keywords, (v) => {
                        return rawResults.searchGroups[v.g] || `Unknown #${v.g}`;
                    });

                    searchGroups = rawResults.searchGroups;
                } catch (e) {
                    console.warn(e);
                }
            }

            dispatch(this.setSearchResults({
                keywords,
                searchGroups,
            }));
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
            const {
                resultsById,
            } = getState().searchResults;
            const results = Object.values(resultsById);
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
     * Loads entities associated with the specified arguments, including (but not necessarily related to) the glossary.
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
                lexicalEntryGroupIds,
                includeOld,
                languageId,
                speechIds,
            } = state.search;

            const {
                groupIdMap,
            } = state.searchResults;

            if (args.lexicalEntryGroupIds !== undefined) {
                lexicalEntryGroupIds = args.lexicalEntryGroupIds;
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

            // `searchResult.word` is the literal matched keyword, which can be an inflected form (e.g.
            // "lond-") distinct from the entry's real headword (`originalWord`, e.g. "lon(de)"). Search by
            // the headword so the result ranks correctly on its own merits, but carry the inflected form
            // along as a separate `inflection` field (URL query param + history state) purely so a future
            // UI can indicate "you searched for the inflected form X" without another backend round-trip.
            const matchedKeyword = args.searchResult.word;
            const isInflection = !! args.searchResult.originalWord && args.searchResult.originalWord !== matchedKeyword;
            const word = isInflection ? args.searchResult.originalWord : matchedKeyword;
            const inflection = isInflection ? matchedKeyword : undefined;
            const normalizedWord = isInflection ? args.searchResult.originalWord : args.searchResult.normalizedWord;

            let language: ILanguageEntity = null;
            let languageShortName: string = null;

            if (languageId !== 0) {
                language = await this._languages.find(languageId, 'id');
                languageShortName = language.shortName;
            }

            const request: IEntitiesRequest = {
                data: {
                    inflection,
                    lexicalEntryGroupIds,
                    includeOld,
                    inflections: true,
                    languageId,
                    normalizedWord,
                    speechIds,
                    word,
                },
                groupId: searchResult.groupId,
            };

            const {
                address,
                title,
            } = this._prepareAddress(request, groupIdMap, languageShortName);

            // When navigating using the browser's back and forward buttons,
            // the state needn't be modified.
            if (updateBrowserHistory && window.history.pushState) {
                const nextState: IBrowserHistoryState = {
                    glossary: true,
                    groupId: searchResult.groupId,
                    inflection,
                    languageShortName,
                    normalizedWord,
                    word,
                };
                window.history.pushState(nextState, title, address);
                addressOwnedByGlossary = true;
            }

            // because most browsers doesn't change the document title when pushing state
            document.title = title;

            this._globalEvents?.fire(this._globalEvents.loadEntity, {
                address,
                groupId: searchResult.groupId,
                languageId: language?.id,
                word,
            });

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
     * Loads one specific lexical entry into the glossary, in place. This is where every reference
     * link on the site ends up — the book browser, the word list, the flashcard deck, discussions.
     *
     * @param entityId lexical entry ID
     * @param updateBrowserHistory (optional) whether to invoke pushState. False when the navigation
     *                             came from the browser's own back and forward buttons.
     */
    public expandSpecificGloss(entityId: number, updateBrowserHistory = true) {
        return async (dispatch: ThunkDispatch<any, any, any>) => {
            try {
                const groupId = SearchResultGlossaryGroupId;
                dispatch({
                    groupId,
                    type: Actions.RequestEntities,
                });

                const entities = await this._api.entity({
                    entityId,
                    groupId,
                });
                this._changeAddressForSpecificGloss(entityId, entities, updateBrowserHistory);
                dispatch(this.setEntities(entities));
            } catch (e) {
                // Ignore errors for now... the gloss probably doesn't exist (invalid reference)
                console.error(e);
            }
        };
    }

    /**
     * Reflects an in-place reference load in the address bar, so the customer can bookmark, share or
     * reload what they are actually looking at.
     *
     * The address is `/wt/{id}` rather than the word-based `/w/{word}` used by search: the glossary is
     * showing exactly one entry, whereas the word address resolves to every entry sharing that word,
     * so reloading it would quietly show something else.
     */
    private _changeAddressForSpecificGloss(lexicalEntryId: number,
        response: IEntitiesResponse<IGlossaryResponse>, updateBrowserHistory: boolean) {
        const entity = response?.entities?.sections?.[0]?.entities?.[0];
        if (! entity) {
            return;
        }

        const word = response.word || entity.word;
        const title = `${capitalize(word)} - Parf Edhellen`;

        if (updateBrowserHistory && window.history.pushState) {
            const nextState: IBrowserHistoryState = {
                glossary: true,
                groupId: SearchResultGlossaryGroupId,
                languageShortName: entity.language?.shortName || null,
                lexicalEntryId,
                normalizedWord: entity.normalizedWord,
                word,
            };
            window.history.pushState(nextState, title, `/wt/${lexicalEntryId}`);
            addressOwnedByGlossary = true;
        }

        // because most browsers doesn't change the document title when pushing state
        document.title = title;
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

            try {
                await this.expandSearchResult(args)(dispatch, getState);
            } catch (err) {
                console.warn(err);
            }
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

    private _prepareAddress(args: IEntitiesRequest, groupIdMap: ISearchGroups, languageShortName: string = null) {
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
            const groupName = groupIdMap[groupId]?.toLocaleLowerCase() || 'entity';
            title = `${capitalizedWord} - Parf Edhellen`;
            address = `/e/${groupName}-${groupId}/${uriEncodedWord}` + (languageShortName ? `/${languageShortName}` : '');
        }

        // embellish the address with configuration values that are not supported by the native URL format
        const supportedSettings: (keyof typeof args.data)[] = [
            'lexicalEntryGroupIds', 'includeOld', 'speechIds', 'inflection',
        ];

        const settings: { [key: string]: any } = {};
        let noOfSettings = 0;
        for (const setting of supportedSettings) {
            const value = args.data[setting];
            if (value === undefined) {
                continue;
            }

            // tslint:disable-next-line: no-bitwise
            settings[toSnakeCase(setting)] = typeof value === 'boolean' ? ~~value : value;
            noOfSettings += 1;
        }

        if (noOfSettings > 0) {
            address += '?' + buildQueryString(settings);
        }

        return {
            address,
            title,
        }
    }
}
