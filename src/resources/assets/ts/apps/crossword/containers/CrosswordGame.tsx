import React, { useCallback, useEffect, useRef, useState } from 'react';
import { connect } from 'react-redux';
import type { ReduxThunkDispatch } from '@root/_types';
import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';
import type { IComponentEvent } from '@root/components/Component._types';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import type IRoleManager from '@root/security/IRoleManager';
import Timer from '../../word-finder/components/Timer';
import GameActions from '../actions/GameActions';
import { CrosswordStage } from '../actions';
import ClueList from '../components/ClueList';
import CompletionStage from '../components/CompletionStage';
import CrosswordGrid, { type ICrosswordGridHandle } from '../components/CrosswordGrid';
import CrosswordGridZoom from '../components/CrosswordGridZoom';
import type { RootReducer } from '../reducers';
import type { ICrosswordInitialState } from '../index._types';
import type { IPuzzleReducerState } from '../reducers/PuzzleReducer';
import type { ISelectionReducerState } from '../reducers/SelectionReducer';
import type { IStageReducerState } from '../reducers/StageReducer';

import './CrosswordGame.scss';
import '../components/CrosswordGrid.scss';
import '../components/ClueList.scss';
import '../components/CompletionStage.scss';

interface ICrosswordGameOwnProps {
    languageId: number;
    date: string;
    initialState: ICrosswordInitialState;
    roleManager?: IRoleManager;
}

interface ICrosswordGameStateProps {
    puzzle: IPuzzleReducerState;
    cells: Record<string, string>;
    check: Record<string, boolean>;
    selection: ISelectionReducerState;
    stage: IStageReducerState;
}

interface ICrosswordGameDispatchProps {
    onLoadPuzzle: (initialState: ICrosswordInitialState) => void;
    onCellClick: (ev: IComponentEvent<{ row: number; col: number }>) => void;
    onClueClick: (ev: IComponentEvent<ICrosswordClue>) => void;
    onCheckAnswers: () => void;
    onRevealClue: (clueNumber: number, direction: string) => void;
    onTimeUpdate: (ev: IComponentEvent<number>) => void;
    onKeyDown: (ev: IComponentEvent<KeyboardEvent>) => void;
    onAdminFill: () => void;
    onPauseTimer: () => void;
    onResumeTimer: () => void;
}

type ICrosswordGameProps = ICrosswordGameOwnProps & ICrosswordGameStateProps & ICrosswordGameDispatchProps;

function CrosswordGame(props: ICrosswordGameProps) {
    const {
        initialState,
        puzzle, cells, check, selection, stage,
        onLoadPuzzle, onCellClick, onClueClick,
        onCheckAnswers, onRevealClue, onTimeUpdate, onKeyDown, onAdminFill,
        onPauseTimer, onResumeTimer,
        roleManager,
    } = props;

    const isAdmin = roleManager?.isAdministrator ?? false;
    const isAuthenticated = !(roleManager?.isAnonymous ?? true);

    const [activeTab, setActiveTab] = useState<'grid' | 'clues'>('grid');
    const [windowFocused, setWindowFocused] = useState(!document.hidden);
    const gridRef = useRef<ICrosswordGridHandle>(null);

    const isPlaying  = stage.stage === CrosswordStage.Playing;
    const isChecking = stage.stage === CrosswordStage.Checking;
    const isComplete = stage.stage === CrosswordStage.Complete;
    const isGameOver = stage.stage === CrosswordStage.GameOver;
    const isLoading  = stage.stage === CrosswordStage.Loading;

    const checksRemaining  = stage.checksRemaining;
    const revealsRemaining = stage.revealsRemaining;
    const isFinalSubmit    = checksRemaining === 0;
    const checkResultsVisible = Object.keys(check).length > 0;

    // Count unique non-null grid cells for "all filled" detection.
    const totalCells = puzzle.grid?.flat().filter(c => c !== null).length ?? 0;
    const filledCount = Object.keys(cells).length;
    const allFilled = totalCells > 0 && filledCount >= totalCells;

    // Load puzzle on mount using server-injected initial state.
    useEffect(() => {
        onLoadPuzzle(initialState);
    }, []);

    // Pause the timer when the tab is hidden; resume and adjust startTime on return.
    useEffect(() => {
        const onVisibilityChange = () => {
            if (document.hidden) {
                setWindowFocused(false);
                onPauseTimer();
            } else {
                setWindowFocused(true);
                onResumeTimer();
            }
        };
        document.addEventListener('visibilitychange', onVisibilityChange);
        return () => document.removeEventListener('visibilitychange', onVisibilityChange);
    }, [onPauseTimer, onResumeTimer]);

    const handleKeyDown = useCallback((ev: IComponentEvent<KeyboardEvent>) => {
        onKeyDown(ev);
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Tab', 'Backspace', 'Delete'].includes(ev.value.key)) {
            ev.value.preventDefault();
        }
    }, [onKeyDown]);

    const handleClueClick = useCallback((ev: IComponentEvent<ICrosswordClue>) => {
        setActiveTab('grid');
        onClueClick(ev);
        // Focus the grid container (not the hidden input) so keyboard input works
        // immediately, without triggering a scroll-to-top in browsers that ignore
        // preventScroll on position:fixed elements inside transformed ancestors.
        gridRef.current?.focusContainer();
    }, [onClueClick]);

    const handleCheck = useCallback(() => {
        onCheckAnswers();
    }, [onCheckAnswers]);

    const handleReveal = useCallback(() => {
        if (!selection.activeClue) return;
        onRevealClue(selection.activeClue.number, selection.activeClue.direction);
    }, [onRevealClue, selection.activeClue]);

    if (isLoading) {
        return (
            <div className="CrosswordGame CrosswordGame--loading text-center py-5">
                <div className="spinner-border text-secondary" role="status">
                    <span className="visually-hidden">Loading puzzle…</span>
                </div>
            </div>
        );
    }

    if (isGameOver) {
        return (
            <div className="CrosswordGame CrosswordGame--game-over text-center py-5">
                <p style={{ fontSize: '3rem' }}>💀</p>
                <h2>Game Over</h2>
                <p className="text-muted">Your answers were not correct. Try again tomorrow!</p>
            </div>
        );
    }

    const activeClueLabel = selection.activeClue
        ? `${selection.activeClue.number} ${selection.activeClue.direction === 'across' ? 'Across' : 'Down'} — ${selection.activeClue.clue}`
        : null;

    return (
        <div className="CrosswordGame">
            {/* Completion banner — shown above the locked grid when solved */}
            {isComplete && (
                <CompletionStage
                    daysCompleted={stage.daysCompleted}
                    secondsElapsed={stage.secondsElapsed}
                    isAssisted={stage.isAssisted}
                    isAuthenticated={isAuthenticated}
                />
            )}

            {/* Active clue banner — always visible while playing, useful on mobile */}
            {activeClueLabel && !isComplete && (
                <div className="CrosswordGame__active-clue" aria-live="polite">
                    {activeClueLabel}
                </div>
            )}

            {/* Mobile tabs */}
            <ul className="nav nav-tabs d-md-none CrosswordGame__tabs mb-2" role="tablist">
                <li className="nav-item">
                    <button
                        className={`nav-link ${activeTab === 'grid' ? 'active' : ''}`}
                        onClick={() => setActiveTab('grid')}
                        role="tab"
                        aria-selected={activeTab === 'grid'}
                    >
                        Grid
                    </button>
                </li>
                <li className="nav-item">
                    <button
                        className={`nav-link ${activeTab === 'clues' ? 'active' : ''}`}
                        onClick={() => setActiveTab('clues')}
                        role="tab"
                        aria-selected={activeTab === 'clues'}
                    >
                        Clues
                    </button>
                </li>
            </ul>

            {/* Desktop: side by side. Mobile: tabs. */}
            <div className="CrosswordGame__layout">
                {/* Grid panel */}
                <div className={`CrosswordGame__grid-panel ${activeTab !== 'grid' ? 'd-none d-md-block' : ''}`}>
                    {/* Toolbar — hidden once the puzzle is complete */}
                    {!isComplete && <div className="CrosswordGame__toolbar mb-2 d-flex align-items-center gap-2">
                        <button
                            className={`btn btn-sm ${isFinalSubmit ? (allFilled ? 'btn-danger' : 'btn-outline-danger') : (allFilled ? 'btn-primary' : 'btn-outline-secondary')}`}
                            onClick={handleCheck}
                            disabled={isChecking || (isFinalSubmit && !allFilled)}
                            title={isFinalSubmit ? 'Final submission — wrong answers end the game' : undefined}
                        >
                            {isChecking
                                ? 'Checking…'
                                : isFinalSubmit
                                    ? 'Submit'
                                    : checkResultsVisible
                                        ? `Hide (${checksRemaining} left)`
                                        : `Check (${checksRemaining})`}
                        </button>
                        <button
                            className="btn btn-sm btn-outline-secondary"
                            onClick={handleReveal}
                            disabled={!selection.activeClue || revealsRemaining === 0}
                            title={revealsRemaining > 0 ? 'Reveal the answer for the selected clue (no completion credit)' : 'No reveals remaining'}
                        >
                            {`Reveal (${revealsRemaining})`}
                        </button>
                        {isAdmin && (
                            <button
                                className="btn btn-sm btn-outline-warning"
                                onClick={onAdminFill}
                                title="Admin: fill in all correct answers"
                            >
                                🔑 Fill All
                            </button>
                        )}
                        <span className="CrosswordGame__timer ms-auto text-muted">
                            ⏱ <Timer
                                onTick={onTimeUpdate}
                                startValue={stage.startTime}
                                tick={isPlaying && windowFocused}
                                value={stage.time}
                            />s
                        </span>
                    </div>}

                    {puzzle.grid && (
                        <CrosswordGridZoom cols={puzzle.grid[0]?.length ?? 0} activeRow={selection.row} activeCol={selection.col}>
                            <CrosswordGrid
                                ref={gridRef}
                                grid={puzzle.grid}
                                clues={puzzle.clues}
                                cells={cells}
                                checkResults={check}
                                activeRow={selection.row}
                                activeCol={selection.col}
                                activeClue={selection.activeClue}
                                onCellClick={onCellClick}
                                onKeyDown={handleKeyDown}
                                locked={isComplete}
                            />
                        </CrosswordGridZoom>
                    )}
                </div>

                {/* Clue panel — mobile + desktop (hidden on very wide screens) */}
                <div className={`CrosswordGame__clue-panel ${activeTab !== 'clues' ? 'd-none d-md-block' : ''}`}>
                    <div className="CrosswordGame__clue-scroll">
                        <ClueList
                            clues={puzzle.clues}
                            activeClue={selection.activeClue}
                            onClueClick={handleClueClick}
                        />
                    </div>
                </div>

                {/* Wide-screen split panels: Across on left, Down on right */}
                <div className="CrosswordGame__across-panel">
                    <div className="CrosswordGame__clue-scroll">
                        <ClueList
                            clues={puzzle.clues}
                            activeClue={selection.activeClue}
                            onClueClick={handleClueClick}
                            direction="across"
                        />
                    </div>
                </div>
                <div className="CrosswordGame__down-panel">
                    <div className="CrosswordGame__clue-scroll">
                        <ClueList
                            clues={puzzle.clues}
                            activeClue={selection.activeClue}
                            onClueClick={handleClueClick}
                            direction="down"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

const mapStateToProps = (state: RootReducer): ICrosswordGameStateProps => ({
    puzzle: state.puzzle,
    cells: state.cells,
    check: state.check,
    selection: state.selection,
    stage: state.stage,
});

const gameActions = new GameActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch): ICrosswordGameDispatchProps => ({
    onLoadPuzzle: (initialState: ICrosswordInitialState) => {
        void dispatch(gameActions.loadPuzzle(initialState) as any);
    },
    onCellClick: (ev: IComponentEvent<{ row: number; col: number }>) => {
        void dispatch(gameActions.selectCell(ev.value.row, ev.value.col) as any);
    },
    onClueClick: (ev: IComponentEvent<ICrosswordClue>) => {
        void dispatch(gameActions.selectClue(ev.value) as any);
    },
    onCheckAnswers: () => {
        void dispatch(gameActions.checkAnswers() as any);
    },
    onRevealClue: (clueNumber, direction) => {
        void dispatch(gameActions.revealClue(clueNumber, direction) as any);
    },
    onAdminFill: () => {
        void dispatch(gameActions.adminFill() as any);
    },
    onTimeUpdate: (ev: IComponentEvent<number>) => {
        dispatch(gameActions.tickTimer(ev.value));
    },
    onPauseTimer: () => {
        void dispatch(gameActions.pauseTimer() as any);
    },
    onResumeTimer: () => {
        void dispatch(gameActions.resumeTimer() as any);
    },
    onKeyDown: (ev: IComponentEvent<KeyboardEvent>) => {
        const key = ev.value.key;
        if (key.length === 1 && /^[a-zA-ZÀ-ÿ]$/u.test(key)) {
            void dispatch(gameActions.enterLetter(key.toUpperCase()) as any);
        } else if (key === 'Backspace' || key === 'Delete') {
            void dispatch(gameActions.deleteLetter() as any);
        } else if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) {
            void dispatch(gameActions.moveSelection(key as any) as any);
        } else if (key === 'Tab') {
            void dispatch(gameActions.advanceToNextWord() as any);
        }
    },
});

const ConnectedCrosswordGame = connect(mapStateToProps, mapDispatchToProps as any)(CrosswordGame) as unknown as React.ComponentType<ICrosswordGameOwnProps>;
export default withPropInjection(ConnectedCrosswordGame, {
    roleManager: DI.RoleManager,
});
