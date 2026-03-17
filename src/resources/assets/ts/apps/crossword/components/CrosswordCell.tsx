import classNames from '@root/utilities/ClassNames';

interface ICrosswordCellProps {
    letter: string | null;       // null = black cell
    userInput: string;
    clueNumber: number | null;
    isActive: boolean;
    isHighlighted: boolean;
    isCorrect: boolean | null;   // null = not checked
    row: number;
    col: number;
    onClick: (row: number, col: number) => void;
}

function CrosswordCell(props: ICrosswordCellProps) {
    const { letter, userInput, clueNumber, isActive, isHighlighted, isCorrect, row, col, onClick } = props;

    if (letter === null) {
        return <div className="CrosswordGrid__cell CrosswordGrid__cell--black" aria-hidden="true" />;
    }

    const handleClick = () => onClick(row, col);

    return (
        <div
            className={classNames(
                'CrosswordGrid__cell',
                isActive       && 'CrosswordGrid__cell--active',
                isHighlighted  && 'CrosswordGrid__cell--highlighted',
                isCorrect === true  && 'CrosswordGrid__cell--correct',
                isCorrect === false && 'CrosswordGrid__cell--incorrect',
            )}
            onClick={handleClick}
            role="gridcell"
            aria-label={clueNumber ? `Cell ${clueNumber}` : undefined}
        >
            {clueNumber !== null && (
                <span className="CrosswordGrid__cell-number">{clueNumber}</span>
            )}
            <span className="CrosswordGrid__cell-letter">{userInput}</span>
        </div>
    );
}

export default CrosswordCell;
