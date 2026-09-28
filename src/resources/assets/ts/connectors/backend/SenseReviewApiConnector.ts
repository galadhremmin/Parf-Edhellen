import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type ISenseReviewApi from './ISenseReviewApi';
import type { IDecision, IDecisionResponse, INextSenseResponse, IRewordResponse } from './ISenseReviewApi';

export default class SenseReviewApiConnector implements ISenseReviewApi {
    constructor(private _api = resolve(DI.BackendApi)) {
    }

    public next(skip: number[] = [], reason?: string): Promise<INextSenseResponse> {
        return this._api.get<INextSenseResponse>('sense-review/next', {
            // a list of IDs cannot ride a query string as an array, so it travels as "12,48,91"
            skip: skip.join(','),
            reason: reason ?? '',
        });
    }

    public decide(senseId: number, decision: IDecision): Promise<IDecisionResponse> {
        return this._api.post<IDecisionResponse>(`sense-review/${senseId}`, decision);
    }

    public reword(senseId: number, sense: string): Promise<IRewordResponse> {
        return this._api.post<IRewordResponse>(`sense-review/${senseId}/reword`, { sense });
    }
}
