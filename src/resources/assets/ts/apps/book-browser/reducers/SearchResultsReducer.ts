import { Actions } from '../actions';
import type {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer._types';

const SearchResultsReducer = (state: ISearchResultState = {
    groups: [],
    resultIds: [],
    resultsByGroupIndex: [],
    resultsById: {},
    selectedId: null,
    groupIdMap: {},
},
    action: ISearchResultReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSearchResults: {
            const {
                resultsById,
                selectedId,
            } = state;
            const {
                searchResults,
            } = action;

            const newGroups: string[] = [];
            const newResultsByGroupIndex: ISearchResult[][] = [];
            const newResultsById: Record<number, ISearchResult> = {};
            const newResultIds: number[] = [];
            let newSelectedId: number|null = null;

            if (searchResults) {
                for (const group of Object.keys(searchResults.keywords)) {
                    newGroups.push(group);

                    const r = searchResults.keywords[group];
                    newResultsByGroupIndex.push(r);
                    r.forEach((v) => {
                        newResultIds.push(v.id);
                        newResultsById[v.id] = v;

                        // carry over selected ID in case that an existing selection is 
                        // in the search results already. This can happen if you search
                        // for _hopa_ expanding it, and then proceeds to look for _hop_.
                        // This will ensure that _hopa_ remains highlighted (since its
                        // expanded) while new search results for _hop_ are populated.
                        if (selectedId !== null) {
                            const oldV = resultsById[selectedId];
                            if (oldV.groupId === v.groupId &&
                                oldV.normalizedWord === v.normalizedWord &&
                                oldV.originalWord === v.originalWord &&
                                oldV.word === v.word) {
                                newSelectedId = v.id;
                            }
                        }
                    });
                }
            }

            return {
                ...state,
                groups: newGroups,
                resultIds: newResultIds,
                resultsByGroupIndex: newResultsByGroupIndex,
                resultsById: newResultsById,
                selectedId: newSelectedId,
                groupIdMap: searchResults?.searchGroups ?? {},
            };
        }

        case Actions.SelectSearchResult: {
            const {
                resultIds,
            } = state;

            let selectedId = action.id ?? null;
            if (selectedId !== null && ! resultIds.includes(selectedId)) {
                selectedId = null;
            }

            return {
                ...state,
                selectedId,
            };
        }
        default:
            return state;
    }
};

export default SearchResultsReducer;
