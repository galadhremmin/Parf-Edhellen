import {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { handleValidationErrors } from '@root/components/Form/Validation';
import IContributionResourceApi, { IContribution, IContributionSaveResponse } from '@root/connectors/backend/IContributionResourceApi';
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
            const r0 = await this._contributionApi.saveGloss(gloss);

            let r1: IContributionSaveResponse = null;
            if (inflections) {
                // Register a related contributed against `response.id`.
                r1 = await this._contributionApi.saveContribution({
                    inflectionGroups: inflections,
                    dependentOnContributionId: r0.id,
                }, 'gloss_infl');
            }

            const url = r0?.url || r1?.url || null;
            if (url) {
                window.location.href = url;
            }
        });
    }

    public saveInflections(inflections: GroupedInflectionsState, contributionId: number = null, glossId: number = null) {
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            const r = await this._contributionApi.saveContribution({
                inflectionGroups: inflections,
                contributionId: contributionId || null,
                glossId: glossId || null,
            }, 'gloss_infl');

            if (r.url) {
                window.location.href = r.url;
            }
        });
    }
}
