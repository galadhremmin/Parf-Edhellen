import type { IFlashcardSkipped } from '@root/connectors/backend/IWordListApi';

import type { IProps } from './SkippedNotice._types';

const reasonPhrase = (skipped: IFlashcardSkipped[]) => {
  const numberOfNoTranslation = skipped.filter((s) => s.reason === 'no-translation').length;
  const numberOfNoDistractors = skipped.length - numberOfNoTranslation;

  const phrases: string[] = [];
  if (numberOfNoTranslation > 0) {
    phrases.push(numberOfNoTranslation === 1
      ? '1 word has no usable translation'
      : `${numberOfNoTranslation} words have no usable translation`);
  }
  if (numberOfNoDistractors > 0) {
    phrases.push(numberOfNoDistractors === 1
      ? '1 word has too few similar words to choose between'
      : `${numberOfNoDistractors} words have too few similar words to choose between`);
  }

  return phrases.join(', ');
};

/**
 * A short deck is never silent: when the server could not turn every requested
 * word into a card, say so, and say why.
 */
const SkippedNotice = (props: IProps) => {
  const { numberOfDealt, numberOfRequested, skipped } = props;

  if (! skipped || skipped.length === 0) {
    return null;
  }

  return <aside className="alert alert-warning word-list-study--skipped" role="status">
    <p className="word-list-study--skipped-summary">
      {`${numberOfRequested} requested, ${numberOfDealt} dealt — ${reasonPhrase(skipped)}.`}
    </p>
    <ul className="word-list-study--skipped-list">
      {skipped.map((entry) => <li key={entry.lexicalEntryId}>{entry.word}</li>)}
    </ul>
  </aside>;
};

export default SkippedNotice;
