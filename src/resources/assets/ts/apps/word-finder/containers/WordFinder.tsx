import React, {
    useEffect,
} from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';

import {
    GameActions,
    GameStage,
} from '../actions';
import CombinePartsStage from '../components/CombinePartsStage';
import GlossList from '../components/GlossList';
// import SuccessStage from '../components/SuccessStage';
import { RootReducer } from '../reducers';

import {
    IContainerProps,
} from '../index._types';

import './WordFinder.scss';
import Timer from '../components/Timer';
import SuccessStage from '../components/SuccessStage';

function WordFinder(props: IContainerProps) {
    const {
        glosses,
        languageId,
        parts,
        selectedParts,
        stage,
        tengwarMode,

        onLoadGame,
        onStageChange,

        // Events for running stage of the game
        onDeselectPart,
        onDiscoverWord,
        onTimeUpdate,
        onSelectPart,
    } = props;

    useEffect(() => {
        if (languageId !== 0) {
            fireEvent('WordFinder', onLoadGame, languageId);
        }
    }, [ languageId ]);

    useEffect(() => {
        const word = selectedParts.map((i) => parts[i].part).join('');
        const gloss = glosses.find((g) => g.available && g.wordForComparison === word);
        if (gloss) {
            fireEvent('WordFinder', onDiscoverWord, gloss.id);
        }
    }, [ selectedParts ]);

    return <div className="WordFinder--container">
        <div className="WordFinder">
            <span className="WordFinder__timer">
                <Timer onTick={onTimeUpdate}
                    startValue={stage.startTime}
                    value={stage.time}
                />
            </span>
            <section>
                <h3>Glosses</h3>
                <GlossList tengwarMode={tengwarMode} glosses={glosses} />
            </section>
            <section>
                {stage.stage === GameStage.Running && <CombinePartsStage
                    onChangeStage={onStageChange}
                    onDeselectPart={onDeselectPart}
                    onSelectPart={onSelectPart}
                    parts={parts}
                    selectedParts={selectedParts}
                    tengwarMode={tengwarMode}
                />}
                {stage.stage === GameStage.Success && <SuccessStage
                    onChangeStage={onStageChange}
                />}
            </section>
        </div>
    </div>;
}

const mapStateToProps = (state: RootReducer) => ({
    glosses: state.glosses,
    parts: state.parts,
    selectedParts: state.selectedParts,
    stage: state.stage,
    tengwarMode: state.stage.tengwarMode,
}) as IContainerProps;

const gameActions = new GameActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onLoadGame: (ev: IComponentEvent<number>) => dispatch(gameActions.loadGame(ev.value)),
    onStageChange: (ev: IComponentEvent<GameStage>) => dispatch(gameActions.setStage(ev.value)),
    onDeselectPart: (ev: IComponentEvent<number>) => dispatch(gameActions.deselectPart(ev.value)),
    onDiscoverWord: (ev: IComponentEvent<number>) => dispatch(gameActions.discoverWord(ev.value)),
    onTimeUpdate: (ev: IComponentEvent<number>) => dispatch(gameActions.setTime(ev.value)),
    onSelectPart: (ev: IComponentEvent<number>) => dispatch(gameActions.selectPart(ev.value)),
}) as Partial<IContainerProps>;

export default connect(mapStateToProps, mapDispatchToProps)(WordFinder);
