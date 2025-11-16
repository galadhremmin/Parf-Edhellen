import { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import type { IStageProps } from '../index._types';
import { GameStage } from '../actions';

import './SuccessStage.scss';

function SuccessStage(props: IStageProps) {
    const {
        onChangeStage,
        startTime,
        time,
    } = props;

    const _onPlayAgain = useCallback(() => {
        void fireEvent('SuccessStage', onChangeStage, GameStage.Loading);
    }, []);

    const timeElapsed = Math.max(0, Math.round((time - startTime) / 1000));
    return <>
        <div className="SuccessStage--fireworks">
            <div className="before"></div>
            <div className="after"></div>
        </div>
        <h3>
            Eglerio!
        </h3>
        <p>You found all words in {timeElapsed} seconds!</p>
        <button className="btn btn-lg btn-primary SuccessStage--play-again" onClick={_onPlayAgain}>Play again</button>
    </>
}

export default SuccessStage;
