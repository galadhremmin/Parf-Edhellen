import {
    forwardRef,
    useCallback,
    useImperativeHandle,
    useRef,
    type CSSProperties,
    type KeyboardEvent as ReactKeyboardEvent,
} from 'react';
import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';
import type { IComponentEvent } from '@root/components/Component._types';
import { fireEvent } from '@root/components/Component';
import CrosswordCell from './CrosswordCell';

interface ICrosswordGridProps {
    grid: (string | null)[][];
    clues: ICrosswordClue[];
    cells: Record<string, string>;
    checkResults: Record<string, boolean>;
    activeRow: number | null;
    activeCol: number | null;
    activeClue: ICrosswordClue | null;
    onCellClick: (ev: IComponentEvent<{ row: number; col: number }>) => void;
    onKeyDown: (ev: IComponentEvent<KeyboardEvent>) => void;
    locked?: boolean;
}

export interface ICrosswordGridHandle {
    focus(): void;
}

const CrosswordGrid = forwardRef<ICrosswordGridHandle, ICrosswordGridProps>(function CrosswordGrid(props, ref) {
    const { grid, clues, cells, checkResults, activeRow, activeCol, activeClue, onCellClick, onKeyDown, locked } = props;
    const containerRef = useRef<HTMLDivElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => containerRef.current?.focus(),
    }));

    // Build a map of cell positions that start clues: "row:col" => clue number
    const clueStartMap = new Map<string, number>();
    for (const clue of clues) {
        const key = `${clue.row}:${clue.col}`;
        if (!clueStartMap.has(key)) {
            clueStartMap.set(key, clue.number);
        }
    }

    // Determine which cells belong to the active clue (for highlighting).
    const highlightedKeys = new Set<string>();
    if (activeClue) {
        const dr = activeClue.direction === 'across' ? 0 : 1;
        const dc = activeClue.direction === 'across' ? 1 : 0;
        for (let i = 0; i < activeClue.length; i++) {
            highlightedKeys.add(`${activeClue.row + i * dr}:${activeClue.col + i * dc}`);
        }
    }

    const handleCellClick = useCallback((row: number, col: number) => {
        if (locked) return;
        void fireEvent('CrosswordGrid', onCellClick, { row, col });
        containerRef.current?.focus();
    }, [onCellClick, locked]);

    const handleKeyDown = useCallback((ev: ReactKeyboardEvent<HTMLDivElement>) => {
        if (locked) return;
        ev.stopPropagation();
        void fireEvent('CrosswordGrid', onKeyDown, ev.nativeEvent);
    }, [onKeyDown, locked]);

    const cols = grid[0]?.length ?? 0;

    return (
        <div
            ref={containerRef}
            className={`CrosswordGrid${locked ? ' CrosswordGrid--locked' : ''}`}
            style={{ '--grid-cols': cols } as CSSProperties}
            role="grid"
            tabIndex={locked ? -1 : 0}
            onKeyDown={handleKeyDown}
            aria-label="Crossword grid"
            aria-readonly={locked || undefined}
        >
            {grid.map((row, rIdx) =>
                row.map((letter, cIdx) => {
                    const key = `${rIdx}:${cIdx}`;
                    const isActive = rIdx === activeRow && cIdx === activeCol;
                    const isHighlighted = highlightedKeys.has(key) && !isActive;
                    const isCorrect = key in checkResults ? checkResults[key] : null;
                    const clueNumber = clueStartMap.get(key) ?? null;

                    return (
                        <CrosswordCell
                            key={key}
                            row={rIdx}
                            col={cIdx}
                            letter={letter}
                            userInput={cells[key] ?? ''}
                            clueNumber={clueNumber}
                            isActive={isActive}
                            isHighlighted={isHighlighted}
                            isCorrect={isCorrect}
                            onClick={handleCellClick}
                        />
                    );
                })
            )}
        </div>
    );
});

export default CrosswordGrid;
