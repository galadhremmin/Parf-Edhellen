import moment from 'moment';
import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';

import { IStageProps } from '../index._types';

import './SuccessStage.scss';
import { GameStage } from '../actions';

function SuccessStage(props: IStageProps) {
    const {
        onChangeStage,
        startTime,
        time,
    } = props;

    const _onPlayAgain = useCallback(() => {
        fireEvent('SuccessStage', onChangeStage, GameStage.Loading);
    }, []);

    return <>
        <div className="SuccessStage--fireworks">
            <div className="before"></div>
            <div className="after"></div>
        </div>
        <h3>
            Eglerio!
        </h3>
        <p>You found all words in {moment.unix(time).diff(moment.unix(startTime), 'seconds')} seconds!</p>
        <button className="btn btn-lg btn-primary SuccessStage--play-again" onClick={_onPlayAgain}>Play again</button>
    </>
}

export default SuccessStage;
