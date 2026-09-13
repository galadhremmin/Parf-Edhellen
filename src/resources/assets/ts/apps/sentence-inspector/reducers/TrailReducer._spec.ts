import {
    describe,
    expect,
    test,
} from '@jest/globals';

import Actions from '../actions/Actions';
import TrailReducer from './TrailReducer';

describe('apps/sentence-inspector/reducers/TrailReducer', () => {
    test('remembers each word as it is opened, once', () => {
        let state = TrailReducer([], { fragmentId: 7283, type: Actions.SelectFragment });
        state = TrailReducer(state, { fragmentId: 7284, type: Actions.SelectFragment });
        state = TrailReducer(state, { fragmentId: 7283, type: Actions.SelectFragment });

        expect(state).toEqual([ 7283, 7284 ]);
    });

    test('ignores a selection being cleared', () => {
        const state = TrailReducer([ 7283 ], { fragmentId: 0, type: Actions.SelectFragment });

        expect(state).toEqual([ 7283 ]);
    });

    test('restores a trail saved on an earlier visit', () => {
        const state = TrailReducer([], { trail: [ 1, 2, 3 ], type: Actions.RestoreTrail });

        expect(state).toEqual([ 1, 2, 3 ]);
    });

    test('starts a new phrase empty', () => {
        const state = TrailReducer([ 1, 2 ], { type: Actions.ReceiveSentence });

        expect(state).toEqual([]);
    });
});
