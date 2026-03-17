interface ICompletionStageProps {
    daysCompleted: number | null;
    secondsElapsed: number | null;
    isAssisted: boolean;
    isAuthenticated: boolean;
}

function CompletionStage(props: ICompletionStageProps) {
    const { daysCompleted, secondsElapsed, isAssisted, isAuthenticated } = props;

    const minutes = secondsElapsed !== null ? Math.floor(secondsElapsed / 60) : null;
    const seconds = secondsElapsed !== null ? secondsElapsed % 60 : null;
    const timeStr = minutes !== null && seconds !== null
        ? minutes > 0
            ? `${minutes}m ${seconds}s`
            : `${seconds}s`
        : null;

    return (
        <div className="CompletionStage text-center">
            {!isAssisted && (
                <div className="SuccessStage--fireworks">
                    <div className="before" />
                    <div className="after" />
                </div>
            )}

            <h2 className="CompletionStage__title">
                {isAssisted ? 'Puzzle complete!' : 'Eglerio!'}
            </h2>

            {isAssisted ? (
                <p className="text-muted">Puzzle solved with hints. Keep practising!</p>
            ) : isAuthenticated && daysCompleted !== null ? (
                <p>
                    You have completed <strong>{daysCompleted}</strong>{' '}
                    {daysCompleted === 1 ? 'puzzle' : 'puzzles'} in this language.
                </p>
            ) : !isAuthenticated ? (
                <p className="text-muted">
                    Complete a crossword every day!{' '}
                    <a href="/login">Log in</a> to track your daily streak.
                </p>
            ) : null}

            {timeStr && (
                <p className="CompletionStage__time text-muted">
                    Solved in <strong>{timeStr}</strong>
                </p>
            )}
        </div>
    );
}

export default CompletionStage;
