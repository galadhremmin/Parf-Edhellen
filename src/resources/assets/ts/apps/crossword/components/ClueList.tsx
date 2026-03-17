import { useCallback } from 'react';
import type { ICrosswordClue } from '@root/connectors/backend/ICrosswordApi';
import type { IComponentEvent } from '@root/components/Component._types';
import { fireEvent } from '@root/components/Component';
import classNames from '@root/utilities/ClassNames';

interface IClueListProps {
    clues: ICrosswordClue[];
    activeClue: ICrosswordClue | null;
    onClueClick: (ev: IComponentEvent<ICrosswordClue>) => void;
    /** When set, only that direction's group is rendered (used in wide-screen split panels). */
    direction?: 'across' | 'down';
}

function ClueList(props: IClueListProps) {
    const { clues, activeClue, onClueClick, direction } = props;

    const across = (!direction || direction === 'across') ? clues.filter(c => c.direction === 'across') : [];
    const down   = (!direction || direction === 'down')   ? clues.filter(c => c.direction === 'down')   : [];

    const handleClick = useCallback((clue: ICrosswordClue) => {
        void fireEvent('ClueList', onClueClick, clue);
    }, [onClueClick]);

    const renderGroup = (title: string, group: ICrosswordClue[]) => (
        <div className="ClueList__section">
            <h6 className="ClueList__heading">{title}</h6>
            <ol className="ClueList__items list-unstyled">
                {group.map(clue => {
                    const isActive = activeClue?.number === clue.number && activeClue?.direction === clue.direction;
                    return (
                        <li
                            key={`${clue.direction}-${clue.number}`}
                            className={classNames('ClueList__item', isActive && 'ClueList__item--active')}
                            onClick={() => handleClick(clue)}
                            role="option"
                            aria-selected={isActive}
                        >
                            <span className="ClueList__item-number">{clue.number}.</span>
                            {' '}{clue.clue}{' '}
                            <span className="ClueList__item-length">({clue.length})</span>
                        </li>
                    );
                })}
            </ol>
        </div>
    );

    return (
        <div className="ClueList" role="listbox" aria-label="Crossword clues">
            {across.length > 0 && renderGroup('Across', across)}
            {down.length > 0 && renderGroup('Down', down)}
        </div>
    );
}

export default ClueList;
