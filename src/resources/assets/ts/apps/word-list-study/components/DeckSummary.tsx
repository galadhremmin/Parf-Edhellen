import { useCallback } from 'react';
import type { MouseEvent } from 'react';

import type { IProps } from './DeckSummary._types';
import SkippedNotice from './SkippedNotice';

const DeckSummary = (props: IProps) => {
  const {
    deck,
    missed,
    numberOfCorrect,
    numberOfWrong,
    onRetryMissed,
    onStudyAgain,
  } = props;

  const onRetryClick = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    onRetryMissed();
  }, [ onRetryMissed ]);

  const onStudyAgainClick = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    onStudyAgain();
  }, [ onStudyAgain ]);

  return <section className="word-list-study--summary">
    <h2>How did you do?</h2>
    <dl className="word-list-study--score">
      <div className="word-list-study--score-item">
        <dt>Correct</dt>
        <dd className="text-success">{numberOfCorrect}</dd>
      </div>
      <div className="word-list-study--score-item">
        <dt>Wrong</dt>
        <dd className="text-danger">{numberOfWrong}</dd>
      </div>
    </dl>

    <SkippedNotice
      numberOfDealt={deck.cards.length}
      numberOfRequested={deck.numberOfRequested}
      skipped={deck.skipped}
    />

    {missed.length > 0 ? <div className="word-list-study--missed">
      <h3>Words to look at again</h3>
      <ul className="word-list-study--missed-list">
        {missed.map((card) => <li key={card.lexicalEntryId}>
          <a href={card.url}>{card.word}</a>
          {' — '}
          <span className="word-list-study--missed-answer">{card.expected}</span>
        </li>)}
      </ul>
    </div> : <p className="text-success">
      You got every card right. Well done!
    </p>}

    <nav className="word-list-study--summary-actions">
      {missed.length > 0 && <a className="btn btn-primary" href="#" onClick={onRetryClick}>
        Retry missed words
      </a>}
      <a className="btn btn-secondary" href="#" onClick={onStudyAgainClick}>
        Study the whole list again
      </a>
    </nav>
  </section>;
};

export default DeckSummary;
