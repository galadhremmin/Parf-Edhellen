import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { handleValidationErrors } from '@root/components/Form/Validation';
import IContributionResourceApi, { IContribution } from '@root/connectors/backend/IContributionResourceApi';
import IGlossResourceApi, { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';
import { DI, resolve } from '@root/di';

import Actions from './Actions';
import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { GroupedInflectionsState, IInflectionGroupState } from '../reducers/InflectionsReducer._types';
import { IChangeTrackerReducerState } from '../reducers/ChangeTrackerReducer._types';

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
            type: Actions.SetGlossField,
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
            type: Actions.SetGlossField,
            value,
        };
    }

    public setLoadedInflections(inflections: IGlossInflection[]) {
        return {
            preloadedInflections: inflections,
            type: Actions.ReceiveInflections,
        }
    }

    public createInflectionGroup() {
        return {
            type: Actions.CreateBlankInflectionGroup,
        };
    }

    public setInflectionGroup(inflectionGroupUuid: string, inflectionGroup: IInflectionGroupState) {
        return {
            inflectionGroupUuid,
            inflectionGroup,
            type: Actions.SetInflectionGroup,
        }
    }

    public unsetInflectionGroup(inflectionGroupUuid: string) {
        return {
            inflectionGroupUuid,
            type: Actions.UnsetInflectionGroup,
        }
    }

    public saveGloss(gloss: IGlossEntity, inflections: GroupedInflectionsState = null) {
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            const response = await this._contributionApi.saveGloss(gloss);
            if (inflections) {
                // Register a related contributed against `response.id`.
                throw new Error('Not implemented yet.');
            }
            if (response.url) {
                window.location.href = response.url;
            }
        });
    }

    public saveInflections(glossId: number, inflections: GroupedInflectionsState) {
        throw new Error('Not implemented yet.');
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            /* TODO!
            const response = await this._contributionApi.saveGloss(gloss);
            if (response.url) {
                window.location.href = response.url;
            }
            */
        });
    }
}
