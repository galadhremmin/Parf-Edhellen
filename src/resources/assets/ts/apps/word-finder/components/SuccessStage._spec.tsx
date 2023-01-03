import {
    describe,
    expect,
    test,
} from '@jest/globals';
import React from 'react';
import { render, screen } from '@testing-library/react';

import SuccessStage from './SuccessStage';
import { DateTime } from 'luxon';

describe('apps/word-finder/components/SuccessStage', () => {
    const GameStageStartTime = 1613351691105;
    const GameStageEndTime = GameStageStartTime + 1000 * (60 * 30); // 30 minutes

    test('mounts and presents the right duration', async () => {
        const { container } = render(<SuccessStage onChangeStage={null} startTime={GameStageStartTime} time={GameStageEndTime} />);

        const duration = DateTime.fromMillis(GameStageEndTime).diff(DateTime.fromMillis(GameStageStartTime), 'seconds').toFormat('s');

        const durationText = await screen.findByText(`You found all words in ${duration} seconds!`);
        expect(durationText).toEqual(expect.anything());
        expect(container.querySelector('.SuccessStage--fireworks')).toEqual(expect.anything());
    });
});
