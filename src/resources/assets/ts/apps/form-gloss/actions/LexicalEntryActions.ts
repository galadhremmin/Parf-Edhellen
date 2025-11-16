import type {
    ReduxThunk,
    ReduxThunkDispatch,
} from '@root/_types';
import { handleValidationErrors } from '@root/components/Form/Validation';
import type IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';
import type { IContribution, IContributionSaveResponse } from '@root/connectors/backend/IContributionResourceApi';
import type ILexicalEntryResourceApi from '@root/connectors/backend/IGlossResourceApi';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IGlossResourceApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type { ILexicalEntryInflection } from '@root/connectors/backend/IBookApi';
import type { GroupedInflectionsState, IInflectionGroupState } from '../reducers/InflectionsReducer._types';
import Actions from './Actions';

export default class LexicalEntryActions {
    constructor(
        private _glossApi: ILexicalEntryResourceApi = resolve(DI.GlossApi),
        private _contributionApi: IContributionResourceApi = resolve(DI.ContributionApi)) {}

    public loadLexicalEntry(lexicalEntryId: number): ReduxThunk {
        return async (dispatch: ReduxThunkDispatch) => {
            const lexicalEntry = await this._glossApi.lexicalEntry(lexicalEntryId);
            dispatch(this.setLoadedLexicalEntry(lexicalEntry));
        };
    }

    public setEditingLexicalEntryId(lexicalEntryId: number) {
        return {
            field: 'id',
            type: Actions.SetLexicalEntryField,
            value: lexicalEntryId || 0,
        };
    }

    public setLoadedLexicalEntry(lexicalEntry: ILexicalEntryEntity) {
        return {
            lexicalEntry,
            type: Actions.ReceiveLexicalEntry,
        };
    }

    public setLexicalEntryField<T extends keyof IContribution<ILexicalEntryEntity>>(field: T, value: IContribution<ILexicalEntryEntity>[T]) {
        return {
            field,
            type: Actions.SetLexicalEntryField,
            value,
        };
    }

    public setLoadedInflections(inflections: ILexicalEntryInflection[]) {
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

    public saveLexicalEntry(lexicalEntry: ILexicalEntryEntity, inflections: GroupedInflectionsState = null) {
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            const r0 = await this._contributionApi.saveLexicalEntry(lexicalEntry);

            let r1: IContributionSaveResponse = null;
            if (inflections) {
                // Register a related contributed against `response.id`.
                r1 = await this._contributionApi.saveContribution({
                    inflectionGroups: inflections,
                    dependentOnContributionId: r0.id,
                }, 'lex_entry_infl');
            }

            const url = r0?.url || r1?.url || null;
            if (url) {
                window.location.href = url;
            }
        });
    }

    public saveInflections(inflections: GroupedInflectionsState, contributionId: number = null, lexicalEntryId: number = null) {
        return (dispatch: ReduxThunkDispatch) => handleValidationErrors(dispatch, async () => {
            const r = await this._contributionApi.saveContribution({
                inflectionGroups: inflections,
                contributionId: contributionId || null,
                lexicalEntryId: lexicalEntryId || null,
            }, 'lex_entry_infl');

            if (r.url) {
                window.location.href = r.url;
            }
        });
    }
}
