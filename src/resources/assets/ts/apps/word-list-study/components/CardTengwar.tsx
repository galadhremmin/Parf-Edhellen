import Tengwar from '@root/components/Tengwar';
import type { IProps } from './CardTengwar._types';

/**
 * Renders a tengwar fragment supplied by the server, or nothing at all when the
 * language has no tengwar mode. The server decides what carries tengwar, so this
 * component never looks at the direction of the deck.
 */
const CardTengwar = (props: IProps) => {
  const { as = 'span', tengwar } = props;

  if (! tengwar) {
    return null;
  }

  return <Tengwar
    as={as}
    mode={tengwar.mode}
    text={tengwar.text}
    transcribe={tengwar.transcribe}
  />;
};

export default CardTengwar;
