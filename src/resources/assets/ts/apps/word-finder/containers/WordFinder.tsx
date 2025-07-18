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

    const {
        stage: currentStage,
    } = stage;

    useEffect(() => {
        if (languageId !== 0 && currentStage === GameStage.Loading) {
            void fireEvent('WordFinder', onLoadGame, languageId);
        }
    }, [ languageId, currentStage ]);

    useEffect(() => {
        const word = selectedParts.map((i) => parts[i].part).join('');
        const gloss = glosses.find((g) => g.available && g.wordForComparison === word);
        if (gloss) {
            void fireEvent('WordFinder', onDiscoverWord, gloss.id);
        }
    }, [ selectedParts ]);

    return <div className="WordFinder--container shadow-lg rounded mb-4">
        <div className="WordFinder">
            <span className="WordFinder__timer">
                <Timer onTick={onTimeUpdate}
                    startValue={stage.startTime}
                    tick={currentStage === GameStage.Running}
                    value={stage.time}
                />
            </span>
            <section>
                <h3>Glosses</h3>
                <GlossList tengwarMode={tengwarMode} glosses={glosses} />
            </section>
            <section>
                {currentStage === GameStage.Running && <CombinePartsStage
                    onChangeStage={onStageChange}
                    onDeselectPart={onDeselectPart}
                    onSelectPart={onSelectPart}
                    parts={parts}
                    selectedParts={selectedParts}
                    tengwarMode={tengwarMode}
                />}
                {currentStage === GameStage.Success && <SuccessStage
                    onChangeStage={onStageChange}
                    startTime={stage.startTime}
                    time={stage.time}
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
    onLoadGame: (ev: IComponentEvent<number>) => {
        void dispatch(gameActions.loadGame(ev.value));
    },
    onStageChange: (ev: IComponentEvent<GameStage>) => {
        dispatch(gameActions.setStage(ev.value));
    },
    onDeselectPart: (ev: IComponentEvent<number>) => {
        dispatch(gameActions.deselectPart(ev.value));
    },
    onDiscoverWord: (ev: IComponentEvent<number>) => {
        dispatch(gameActions.discoverWord(ev.value));
    },
    onTimeUpdate: (ev: IComponentEvent<number>) => {
        dispatch(gameActions.setTime(ev.value));
    },
    onSelectPart: (ev: IComponentEvent<number>) => {
        dispatch(gameActions.selectPart(ev.value));
    },
}) as Partial<IContainerProps>;

export default connect(mapStateToProps, mapDispatchToProps)(WordFinder);
