import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';
import SharedReference from '@root/utilities/SharedReference';

import Actions from './Actions';

export default class GlossActions {
    constructor(private _api = SharedReference.getInstance(BookApiConnector)) {}

    public gloss(glossId: number): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            // const gloss = await this._api.gloss(glossId);
            dispatch(this.setGloss(null));
        };
    }

    public setGloss(gloss: IGlossEntity) {
        return {
            gloss,
            type: Actions.ReceiveGloss,
        };
    }

    public setField(field: keyof IGlossEntity, value: any) {
        return {
            field,
            type: Actions.SetField,
            value,
        };
    }
}
