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
            let {
                selectedId,
            } = state;
            const {
                searchResults,
            } = action;

            const groups: string[] = [];
            const resultsByGroupIndex: ISearchResult[][] = [];
            const resultsById: Record<number, ISearchResult> = {};
            const resultIds: number[] = [];

            for (const group of Object.keys(searchResults.keywords)) {
                groups.push(group);

                const r = searchResults.keywords[group];
                resultsByGroupIndex.push(r);
                r.forEach((v) => {
                    resultIds.push(v.id);
                    resultsById[v.id] = v;
                });
            }

            // `null` rather than the first result: a result is only selected when the customer
            // actually picks one, so that a fresh set of search results highlights nothing.
            if (! resultIds.includes(selectedId)) {
                selectedId = null;
            }

            return {
                ...state,
                groups,
                resultIds,
                resultsByGroupIndex,
                resultsById,
                selectedId,
                groupIdMap: searchResults.searchGroups,
            };
        }

        case Actions.SelectSearchResult: {
            const {
                resultIds,
            } = state;

            let selectedId = action.id;
            if (! resultIds.includes(selectedId)) {
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
