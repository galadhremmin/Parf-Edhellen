import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { handleValidationErrors } from '@root/components/Form/Validation';
import IContributionResourceApi, { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import IGlossResourceApi, { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';
import { DI, resolve } from '@root/di';

import Actions from './Actions';

export default class GlossActions {
    constructor(
        private _glossApi: IGlossResourceApi = resolve(DI.GlossApi),
        private _contributionApi: IContributionResourceApi = resolve(DI.ContributionApi)) {}

    public loadGloss(glossId: number): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const gloss = await this._glossApi.gloss(glossId);
            dispatch(this.setLoadedGloss(gloss));
        };
    }

    public setEditingGlossId(glossId: number) {
        return {
            field: 'id',
            type: Actions.SetField,
            value: glossId || 0,
        };
    }

    public setLoadedGloss(gloss: IGlossEntity) {
        return {
            gloss,
            type: Actions.ReceiveGloss,
        };
    }

    public setGlossField<T extends keyof IContribution<IGlossEntity>>(field: T, value: IContribution<IGlossEntity>[T]) {
        return {
            field,
            type: Actions.SetField,
            value,
        };
    }

    public saveGloss(gloss: IGlossEntity) {
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            const response = await this._contributionApi.saveGloss(gloss);
            if (response.url) {
                window.location.href = response.url;
            }
        });
    }
}
