import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';

import '@root/utilities/Enzyme';
import SuccessStage from './SuccessStage';
import { DateTime } from 'luxon';

describe('apps/word-finder/components/SuccessStage', () => {
    const GameStageStartTime = 1613351691105;
    const GameStageEndTime = GameStageStartTime + 1000 * (60 * 30); // 30 minutes

    let wrapper: ReactWrapper;
    before(() => {
        wrapper = mount(<SuccessStage onChangeStage={null} startTime={GameStageStartTime} time={GameStageEndTime} />);
    });

    it('mounts and presents the right duration', () => {
        const duration = DateTime.fromMillis(GameStageEndTime).diff(DateTime.fromMillis(GameStageStartTime), 'seconds').toFormat('s');
        expect(wrapper.text()).to.contain(`You found all words in ${duration} seconds!`);
        expect(wrapper.find('.SuccessStage--fireworks')).to.exist;
    });
});
