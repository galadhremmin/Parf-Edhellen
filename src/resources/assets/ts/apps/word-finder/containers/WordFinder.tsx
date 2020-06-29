import React, {
    useCallback,
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

function WordFinder(props: IContainerProps) {
    const {
        glosses,
        languageId,
        parts,
        selectedParts,
        stage,

        onLoadGame,

        // Events for running stage of the game
        onDeselectPart,
        onDiscoverWord,
        onSelectPart,
    } = props;

    useEffect(() => {
        if (languageId !== 0) {
            fireEvent('WordFinder', onLoadGame, languageId);
        }
    }, [ languageId ]);

    useEffect(() => {
        const word = selectedParts.map((i) => parts[i].part).join('');
        const gloss = glosses.find((g) => g.available && g.word === word);
        if (gloss) {
            fireEvent('WordFinder', onDiscoverWord, gloss.id);
        }
    }, [ selectedParts ]);

    return <>
        <section>
            <GlossList glosses={glosses} />
        </section>
        <section>
            {stage.stage === GameStage.Running && <CombinePartsStage
                onSelectPart={onSelectPart}
                onDeselectPart={onDeselectPart}
                parts={parts}
                selectedParts={selectedParts}
            />}
        </section>
    </>;
}

const mapStateToProps = (state: RootReducer) => ({
    glosses: state.glosses,
    parts: state.parts,
    selectedParts: state.selectedParts,
    stage: state.stage,
}) as IContainerProps;

const gameActions = new GameActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onLoadGame: (ev: IComponentEvent<number>) => dispatch(gameActions.loadGame(ev.value)),

    onDeselectPart: (ev: IComponentEvent<number>) => dispatch(gameActions.deselectPart(ev.value)),
    onDiscoverWord: (ev: IComponentEvent<number>) => dispatch(gameActions.discoverWord(ev.value)),
    onSelectPart: (ev: IComponentEvent<number>) => dispatch(gameActions.selectPart(ev.value)),
}) as Partial<IContainerProps>;

export default connect(mapStateToProps, mapDispatchToProps)(WordFinder);
