import classNames from '@root/utilities/ClassNames';

import type { IProps } from './WordButton._types';

/**
 * A word in the text.
 *
 * The dotted gilded rule beneath it is the whole invitation of the page: fragments used to
 * be `color: inherit; text-decoration: none`, indistinguishable from prose, and the page
 * compensated with a sentence telling you to click. Words an editor has written about get a
 * solid rule and a lozenge, because those are the best reading here and nothing used to say so.
 */
export default function WordButton(props: IProps) {
    const {
        fragment,
        highlightChanged,
        isOpened,
        isSelected,
        onClick,
        plain,
        text,
    } = props;

    return <button type="button"
        className={classNames('phrase-word', {
            'phrase-word--changed': ! plain && highlightChanged && fragment.isChanged,
            'phrase-word--noted': ! plain && fragment.hasNote,
            'phrase-word--opened': ! plain && isOpened && ! isSelected,
            'phrase-word--plain': plain,
            'is-selected': isSelected,
        })}
        data-fragment={fragment.id}
        aria-pressed={isSelected}
        aria-label={fragment.gloss ? `${fragment.fragment} — ${fragment.gloss}` : fragment.fragment}
        onClick={() => onClick(fragment.id)}>
        {text}
    </button>;
}
