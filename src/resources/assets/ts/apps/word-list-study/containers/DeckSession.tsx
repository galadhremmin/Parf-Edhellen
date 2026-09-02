import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { MouseEvent } from 'react';

import Spinner from '@root/components/Spinner';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import type {
  FlashcardDirection,
  IFlashcardAnswer,
  IFlashcardCard,
  IFlashcardDeck,
  IFlashcardOption,
  IFlashcardResults,
} from '@root/connectors/backend/IWordListApi';
import type { IMissedCard } from '../components/DeckSummary._types';
import type { IProps } from '../index._types';
import type { DeckPhase } from './DeckSession._types';

import DeckCard from '../components/DeckCard';
import DeckSummary from '../components/DeckSummary';
import SkippedNotice from '../components/SkippedNotice';

import './DeckSession.scss';

/** The only place in the app that is allowed to know about the direction. */
const instructionFor = (direction: FlashcardDirection) => direction === 'reverse'
  ? 'Which word means this?'
  : 'What does this mean?';

/**
 * Fallback used when the results request fails: the deck already carries the
 * correct option, so the session can still show an honest summary offline.
 */
const scoreLocally = (deck: IFlashcardDeck, answers: IFlashcardAnswer[]): IFlashcardResults => {
  const cardsByEntryId = new Map<number, IFlashcardCard>(
    deck.cards.map((card) => [ card.lexicalEntryId, card ]),
  );

  const cards = answers.map((answer) => {
    const card = cardsByEntryId.get(answer.lexicalEntryId);
    const expected = card ? card.back.answer : '';

    return {
      actual: answer.answer,
      correct: expected !== '' && expected === answer.answer,
      expected,
      lexicalEntryId: answer.lexicalEntryId,
      url: card ? card.back.url : '',
      word: card ? card.back.word : String(answer.lexicalEntryId),
    };
  });

  const numberOfCorrect = cards.filter((card) => card.correct).length;

  return {
    cards,
    numberOfCorrect,
    numberOfWrong: cards.length - numberOfCorrect,
  };
};

export function DeckSession(props: IProps) {
  const {
    api,
    direction,
    wordListId,
    wordListName,
  } = props;

  const [ phase, setPhase ] = useState<DeckPhase>('idle');
  const [ deck, setDeck ] = useState<IFlashcardDeck>(null);
  const [ cardIndex, setCardIndex ] = useState<number>(0);
  const [ selectedOption, setSelectedOption ] = useState<IFlashcardOption>(null);
  const [ correct, setCorrect ] = useState<boolean>(false);
  const [ results, setResults ] = useState<IFlashcardResults>(null);

  // The answers buffer lives in a ref so the `visibilitychange` handler never
  // closes over a stale copy of it.
  const answersRef = useRef<IFlashcardAnswer[]>([]);
  const submittedRef = useRef<boolean>(true);
  const dealtRef = useRef<boolean>(false);

  const deal = useCallback(async (lexicalEntryIds?: number[]) => {
    setPhase('dealing');
    setResults(null);
    setSelectedOption(null);
    setCorrect(false);
    setCardIndex(0);
    answersRef.current = [];
    submittedRef.current = false;

    try {
      const limit = lexicalEntryIds ? lexicalEntryIds.length : undefined;
      const response = await api.deck(wordListId, direction, limit, lexicalEntryIds);

      setDeck(response.deck);

      if (response.deck.cards.length === 0) {
        submittedRef.current = true;
        setPhase('empty');
      } else {
        setPhase('answering');
      }
    } catch (_) {
      submittedRef.current = true;
      setPhase('failed');
    }
  }, [ api, direction, wordListId ]);

  useEffect(() => {
    if (dealtRef.current) {
      return;
    }

    dealtRef.current = true;
    void deal();
  }, [ deal ]);

  // Abandoned sessions still count: flush whatever the user managed to answer.
  useEffect(() => {
    const onVisibilityChange = () => {
      if (document.visibilityState !== 'hidden') {
        return;
      }
      if (submittedRef.current || answersRef.current.length === 0) {
        return;
      }

      submittedRef.current = true;
      void api.deckResults(wordListId, direction, answersRef.current)
        .catch((): void => {
          // Best-effort flush; an abandoned session must never raise.
        });
    };

    document.addEventListener('visibilitychange', onVisibilityChange);
    return () => {
      document.removeEventListener('visibilitychange', onVisibilityChange);
    };
  }, [ api, direction, wordListId ]);

  const submit = useCallback(async () => {
    const answers = answersRef.current;
    submittedRef.current = true;
    setPhase('submitting');

    try {
      const response = await api.deckResults(wordListId, direction, answers);
      setResults(response.results);
    } catch (_) {
      setResults(scoreLocally(deck, answers));
    }

    setPhase('summary');
  }, [ api, deck, direction, wordListId ]);

  const card = deck && cardIndex < deck.cards.length ? deck.cards[cardIndex] : null;

  const onSelect = useCallback((option: IFlashcardOption) => {
    if (phase !== 'answering' || card === null) {
      return;
    }

    // Scoring is local: the server already told us which option is correct, so
    // the card can flip without a round-trip. Results are submitted once, at
    // the end, and the server re-derives correctness there.
    setCorrect(option.key === card.back.correctOptionKey);
    setSelectedOption(option);

    answersRef.current = [
      ...answersRef.current,
      {
        answer: option.text,
        glossId: card.glossId,
        lexicalEntryId: card.lexicalEntryId,
      },
    ];

    setPhase('flipped');
  }, [ card, phase ]);

  const onNext = useCallback(() => {
    if (phase !== 'flipped') {
      return;
    }

    if (cardIndex + 1 >= deck.cards.length) {
      void submit();
      return;
    }

    setSelectedOption(null);
    setCorrect(false);
    setCardIndex((index) => index + 1);
    setPhase('answering');
  }, [ cardIndex, deck, phase, submit ]);

  const missed = useMemo<IMissedCard[]>(() => {
    if (! deck || ! results) {
      return [];
    }

    const cardsByEntryId = new Map<number, IFlashcardCard>(
      deck.cards.map((deckCard) => [ deckCard.lexicalEntryId, deckCard ]),
    );

    return results.cards.filter((result) => ! result.correct).map((result) => {
      const deckCard = cardsByEntryId.get(result.lexicalEntryId);

      return {
        actual: result.actual,
        expected: result.expected || (deckCard ? deckCard.back.answer : ''),
        lexicalEntryId: result.lexicalEntryId,
        // The server names the word and its address, so the summary survives a deck that has
        // already been replaced. The deck is only consulted when scoring fell back to local.
        url: result.url || (deckCard ? deckCard.back.url : null),
        word: result.word || (deckCard ? deckCard.back.word : String(result.lexicalEntryId)),
      };
    });
  }, [ deck, results ]);

  const onRetryMissed = useCallback(() => {
    const lexicalEntryIds = missed.map((missedCard) => missedCard.lexicalEntryId);
    if (lexicalEntryIds.length === 0) {
      return;
    }

    void deal(lexicalEntryIds);
  }, [ deal, missed ]);

  const onStudyAgain = useCallback(() => {
    void deal();
  }, [ deal ]);

  const onRetryDealClick = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    void deal();
  }, [ deal ]);

  const renderBody = () => {
    switch (phase) {
      case 'idle':
      case 'dealing':
        return <div className="word-list-study--loading">
          <Spinner />
          <p>Shuffling your deck…</p>
        </div>;

      case 'submitting':
        return <div className="word-list-study--loading">
          <Spinner />
          <p>Counting your score…</p>
        </div>;

      case 'failed':
        return <div className="alert alert-danger" role="alert">
          <p>The deck could not be dealt. Please try again.</p>
          <a className="btn btn-primary" href="#" onClick={onRetryDealClick}>
            Try again
          </a>
        </div>;

      case 'empty':
        return <>
          <div className="alert alert-info" role="alert">
            There are no words in this list that can be turned into flashcards yet.
          </div>
          {deck && <SkippedNotice
            numberOfDealt={0}
            numberOfRequested={deck.numberOfRequested}
            skipped={deck.skipped}
          />}
        </>;

      case 'summary':
        return <DeckSummary
          deck={deck}
          missed={missed}
          numberOfCorrect={results ? results.numberOfCorrect : 0}
          numberOfWrong={results ? results.numberOfWrong : 0}
          onRetryMissed={onRetryMissed}
          onStudyAgain={onStudyAgain}
        />;

      default:
        // Only the current card is ever rendered: every `<Tengwar>` instance
        // fires an asynchronous transcription, so a whole deck's worth of
        // options would be dozens of them.
        return card && <>
          <SkippedNotice
            numberOfDealt={deck.cards.length}
            numberOfRequested={deck.numberOfRequested}
            skipped={deck.skipped}
          />
          <p className="word-list-study--progress">
            {`Card ${cardIndex + 1} of ${deck.cards.length}`}
          </p>
          <DeckCard
            card={card}
            correct={correct}
            flipped={phase === 'flipped'}
            instruction={instructionFor(direction)}
            isLastCard={cardIndex + 1 === deck.cards.length}
            onNext={onNext}
            onSelect={onSelect}
            selectedOption={selectedOption}
          />
        </>;
    }
  };

  return <div className="word-list-study">
    <header className="word-list-study--header">
      <h1>{deck ? deck.wordListName : wordListName}</h1>
    </header>
    {renderBody()}
  </div>;
}

export default withPropInjection(DeckSession, {
  api: DI.WordListApi,
});
