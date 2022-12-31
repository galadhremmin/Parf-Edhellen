import { Actions } from '../actions';
import {
    ISearchResult,
    ISearchResultReducerAction,
    ISearchResultState,
} from './SearchResultsReducer._types';

const SearchResultsReducer = (state: ISearchResultState = {
    groups: [],
    resultIds: [],
    resultsByGroupIndex: [],
    resultsById: {},
    selectedId: 0,
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

            for (const group of searchResults.keywords.keys()) {
                groups.push(group);

                const r = searchResults.keywords.get(group);
                resultsByGroupIndex.push(r);
                r.forEach((v) => {
                    resultIds.push(v.id);
                    resultsById[v.id] = v;
                });
            }

            if (! resultIds.includes(selectedId)) {
                selectedId = 0;
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
                selectedId = 0;
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
