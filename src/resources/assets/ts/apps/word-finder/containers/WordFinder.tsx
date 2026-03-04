import React, {
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import { connect } from 'react-redux';
import classNames from '@root/utilities/ClassNames';

import type { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import type { IComponentEvent } from '@root/components/Component._types';

import {
    GameActions,
    GameStage,
} from '../actions';
import { splitWord } from '../utilities/word-splitter';
import CombinePartsStage from '../components/CombinePartsStage';
import GlossList from '../components/GlossList';
// import SuccessStage from '../components/SuccessStage';
import type { RootReducer } from '../reducers';
import type { IContainerProps } from '../index._types';

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

    const MAX_HINTS = 3;
    const [ hintsUsed, setHintsUsed ] = useState(0);
    const [ hintPartId, setHintPartId ] = useState<number | null>(null);
    const hintTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const onHint = useCallback(() => {
        // If the user has already started typing, hint the next part of the word they're working on.
        // Fall back to the first part of the first available word if nothing matches.
        const currentTyped = selectedParts.map((i) => parts[i].part).join('');

        // Hints only cost when the user already has a fragment selected.
        if (currentTyped.length > 0 && hintsUsed >= MAX_HINTS) {
            return;
        }
        let targetPart: string | null = null;

        if (currentTyped.length > 0) {
            const activeGloss = glosses.find((g) =>
                g.available &&
                g.wordForComparison.startsWith(currentTyped) &&
                g.wordForComparison !== currentTyped,
            );
            if (activeGloss) {
                targetPart = splitWord(activeGloss.wordForComparison)[selectedParts.length] ?? null;
            }
        }

        if (! targetPart) {
            const firstGloss = glosses.find((g) => g.available);
            if (firstGloss) {
                targetPart = splitWord(firstGloss.wordForComparison)[0];
            }
        }

        if (! targetPart) {
            return;
        }

        const match = parts.find((p) => p.available && ! p.selected && p.part === targetPart);
        if (! match) {
            return;
        }
        if (hintTimerRef.current) {
            clearTimeout(hintTimerRef.current);
        }
        setHintPartId(match.id);
        if (currentTyped.length > 0) {
            setHintsUsed((n) => n + 1);
        }
        hintTimerRef.current = setTimeout(() => setHintPartId(null), 3000);
    }, [ glosses, parts, selectedParts, hintsUsed ]);

    useEffect(() => {
        setHintPartId(null);
    }, [ selectedParts ]);

    useEffect(() => {
        if (languageId !== 0 && currentStage === GameStage.Loading) {
            void fireEvent('WordFinder', onLoadGame, languageId);
        }
    }, [ currentStage ]);

    useEffect(() => {
        const word = selectedParts.map((i) => parts[i].part).join('');
        const gloss = glosses.find((g) => g.available && g.wordForComparison === word);
        if (gloss) {
            void fireEvent('WordFinder', onDiscoverWord, gloss.id);
        }
    }, [ selectedParts ]);

    return <div className="WordFinder--container mb-4">
        <div className="WordFinder">
            <span className="WordFinder__timer">
                <Timer onTick={onTimeUpdate}
                    startValue={stage.startTime}
                    tick={currentStage === GameStage.Running}
                    value={stage.time}
                />
            </span>
            <section>
                <div className="WordFinder__progress" aria-label="Progress">
                    {glosses.map((g) => (
                        <span key={g.gloss} className={classNames('WordFinder__pip', { 'WordFinder__pip--found': ! g.available })} />
                    ))}
                </div>
                <h3>Glosses</h3>
                <GlossList tengwarMode={tengwarMode} glosses={glosses} />
            </section>
            <section>
                {currentStage === GameStage.Running && <CombinePartsStage
                    onChangeStage={onStageChange}
                    onDeselectPart={onDeselectPart}
                    onHint={onHint}
                    onSelectPart={onSelectPart}
                    hintPartId={hintPartId}
                    hintsRemaining={MAX_HINTS - hintsUsed}
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
