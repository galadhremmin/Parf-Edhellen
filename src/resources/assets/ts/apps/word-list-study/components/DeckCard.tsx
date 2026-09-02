import { useCallback, useRef } from 'react';
import type { MouseEvent } from 'react';

import { Card, SideOfTheCard, Table } from '@root/components/FlipCard';
import HtmlInject from '@root/components/HtmlInject';
import TextIcon from '@root/components/TextIcon';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type { IBackFace, IProps } from './DeckCard._types';
import CardOption from './CardOption';
import CardTengwar from './CardTengwar';

const DeckCard = (props: IProps) => {
  const {
    card,
    correct,
    flipped,
    instruction,
    isLastCard,
    onNext,
    onSelect,
    selectedOption,
  } = props;

  const onNextClick = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    onNext();
  }, [ onNext ]);

  // The back face keeps showing the card that was just answered for as long as
  // the flip back is running. Without this it empties the moment `flipped`
  // turns false, and the first half of the rotation — the half where the back
  // is the side facing the viewer — shows a blank card.
  //
  // Derived during render rather than in an effect: an effect lands a frame
  // late, which is exactly the frame where the back is about to become visible.
  const backFaceRef = useRef<IBackFace | null>(null);
  if (flipped) {
    backFaceRef.current = { card, correct, selectedOption };
  }
  const backFace = backFaceRef.current;

  /**
   * Loads the entry in the glossary already on the page rather than navigating
   * away, so a study session is not lost by looking a word up. The book browser
   * is mounted by the default layout, so its `loadReference` listener is always
   * there. The href is kept, so middle click, open-in-new-tab and the context
   * menu still work; only an unmodified left click is intercepted.
   *
   * Bound to the retained back face, not to the incoming card: those differ for
   * the length of the flip back, and the link belongs to the word on screen.
   */
  const onEntryOpen = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    if (ev.button !== 0 || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey || backFace === null) {
      return;
    }

    ev.preventDefault();

    const globalEvents = resolve(DI.GlobalEvents);
    globalEvents?.fire(globalEvents.loadReference, {
      lexicalEntryId: backFace.card.lexicalEntryId,
    });
  }, [ backFace ]);


  // Deliberately NOT keyed on card.cardId. A key remounts the flipper, and a
  // freshly mounted element has no previous transform for CSS to animate from,
  // so the card would snap to the front instead of turning back over. Nothing
  // bleeds through in the meantime: both faces set `backface-visibility:
  // hidden`, so only the side facing the viewer is ever drawn.
  return <Table cardState={flipped ? SideOfTheCard.Back : SideOfTheCard.Front}>
    <Card side={SideOfTheCard.Front}>
      <header>
        <h1>
          {card.prompt}
          <CardTengwar tengwar={card.promptTengwar} />
        </h1>
      </header>
      <p className="word-list-study--instruction">{instruction}</p>
      <nav>
        <ul className="list-group">
          {card.options.map((option) => <CardOption
            key={option.key}
            disabled={flipped}
            onSelect={onSelect}
            option={option}
          />)}
        </ul>
      </nav>
    </Card>
    <Card side={SideOfTheCard.Back}>
      {backFace && <>
        <header>
          <h1>
            {backFace.card.back.word}
            <CardTengwar tengwar={backFace.card.promptTengwar} />
          </h1>
        </header>
        {backFace.correct ? <p className="text-success word-list-study--verdict">
          <TextIcon icon="ok" />
          {' '}
          That&apos;s right!
        </p> : <p className="text-danger word-list-study--verdict">
          <TextIcon icon="warning-sign" />
          {' '}
          Not quite.
        </p>}
        <p className="word-list-study--answer">{backFace.card.back.answer}</p>
        {! backFace.correct && backFace.selectedOption && <p className="word-list-study--picked">
          You picked &ldquo;{backFace.selectedOption.text}&rdquo;.
        </p>}
        {backFace.card.back.comments && <div className="word-list-study--comments">
          <HtmlInject html={backFace.card.back.comments} />
        </div>}
        {backFace.card.back.source && <span className="word-list-study--source">[{backFace.card.back.source}]</span>}
        <nav className="word-list-study--card-actions">
          <a className="word-list-study--entry-link"
             href={backFace.card.back.url}
             onClick={onEntryOpen}>
            Open the dictionary entry
          </a>
          <a className="btn btn-primary" href="#" onClick={onNextClick}>
            {isLastCard ? 'See your results' : 'Next card'}
          </a>
        </nav>
      </>}
    </Card>
  </Table>;
};

export default DeckCard;
