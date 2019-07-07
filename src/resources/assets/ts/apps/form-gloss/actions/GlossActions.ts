import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import GlossResourceApiConnector from '@root/connectors/backend/GlossResourceApiConnector';
import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';
import SharedReference from '@root/utilities/SharedReference';

import Actions from './Actions';

export default class GlossActions {
    constructor(private _api = SharedReference.getInstance(GlossResourceApiConnector)) {}

    public gloss(glossId: number): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const gloss = await this._api.gloss(glossId);
            dispatch(this.setGloss(gloss));
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

    public saveGloss(gloss: IGlossEntity) {
        return async (dispatch: ReduxThunkDispatch) => {
            try {
                const response = await this._api.saveGloss(gloss);
                console.log(['success', response]);
            } catch (e) {
                console.log(e);
            }
        };
    }
}
