import {
    Dispatch,
} from 'redux';

import BookApiConnector from '../../../connectors/backend/BookApiConnector';
import {
    IFindActionEntity,
} from '../../../connectors/backend/BookApiConnector.types';
import {
    mapArray,
} from '../../../utilities/func/mapper';
import {
    Actions,
} from '../reducers/constants';
import {
    ISearchActionState,
    ISearchResultState,
} from './SearchActions.types';

export default class SearchActions {
    constructor(private _api: BookApiConnector = new BookApiConnector()) {
    }

    public search(args: ISearchActionState) {
        return async (dispatch: Dispatch) => {
            dispatch({
                type: Actions.RequestSearchResults,
                ...args,
            });

            const rawResults = await this._api.find(args);
            const results = mapArray<IFindActionEntity, ISearchResultState>({
                normalizedWord: 'nk',
                originalWord: 'ok',
                word: 'k',
            }, rawResults);

            dispatch(this.setSearchResults(results));
        };
    }

    public setSearchResults(items: ISearchResultState[]) {
        return {
            items,
            type: Actions.ReceiveSearchResults,
        };
    }
}
