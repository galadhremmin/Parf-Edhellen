import { useCallback } from 'react';
import type { MouseEvent } from 'react';

import type { IProps } from './CardOption._types';
import CardTengwar from './CardTengwar';

/**
 * A single answer option. The option is passed back through `onSelect` rather
 * than read from `event.target`, which would resolve to the inner tengwar span
 * whenever the option carries a transcription.
 */
const CardOption = (props: IProps) => {
  const { disabled, onSelect, option } = props;

  const onClick = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();

    if (! disabled) {
      onSelect(option);
    }
  }, [ disabled, onSelect, option ]);

  return <li className="list-group-item word-list-study--option">
    <a href="#" onClick={onClick} aria-disabled={disabled}>
      <span className="word-list-study--option-text">{option.text}</span>
      <CardTengwar tengwar={option.tengwar} />
    </a>
  </li>;
};

export default CardOption;
