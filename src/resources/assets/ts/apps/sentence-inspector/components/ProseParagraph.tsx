import classNames from '@root/utilities/ClassNames';

import type { IProps } from './ProseParagraph._types';

/**
 * A paragraph of prose, set as interlinear stacks.
 *
 * Verse can afford three separate lines -- tengwar, transcription, translation -- because
 * it wraps rarely. A paragraph of forty-three words cannot: by the second wrap the tengwar
 * line and the transcription have lost each other, and the reader is matching words by
 * counting. So each word becomes its own small [tengwar / latin] unit and the paragraph
 * flows as text. The tengwar drops from display size to annotation size and a fainter ink:
 * in verse it is the voice, in prose it is the apparatus.
 */
export default function ProseParagraph(props: IProps) {
    const {
        display,
        fragments,
        isActive,
        line,
        onSelectFragment,
        selectedFragmentId,
        trail,
    } = props;

    return <div className={classNames('phrase-para', { 'is-active': isActive })}
        data-line={line.n}>
        <div className="phrase-line__n" aria-hidden="true">&#182; {line.n}</div>
        <div className="phrase-para__body">
            <p className="phrase-para__flow">
                {line.latin.map((token, i) => {
                    const fragment = token.fragmentId ? fragments.get(token.fragmentId) : null;
                    if (! fragment) {
                        // Whitespace between stacks is the flex gap's job; only visible
                        // punctuation is worth a mark of its own.
                        const text = token.text.trim();
                        return text === ''
                            ? null
                            : <span key={`s-${i}`} className="phrase-stack--separator">{text}</span>;
                    }

                    const isSelected = fragment.id === selectedFragmentId;
                    const tengwar = line.tengwarByFragment[fragment.id] || fragment.tengwar;

                    return <button key={`w-${i}`}
                        type="button"
                        data-fragment={fragment.id}
                        aria-pressed={isSelected}
                        aria-label={fragment.gloss ? `${fragment.fragment} — ${fragment.gloss}` : fragment.fragment}
                        className={classNames('phrase-stack', {
                            'phrase-stack--changed': display.changedForms && fragment.isChanged,
                            'phrase-stack--noted': fragment.hasNote,
                            'phrase-stack--opened': trail.has(fragment.id) && ! isSelected,
                            'is-selected': isSelected,
                        })}
                        onClick={() => onSelectFragment(fragment.id)}>
                        {display.tengwar && <span className="phrase-stack__tengwar tengwar">{tengwar}</span>}
                        {display.latin && <span className="phrase-stack__latin">{token.text}</span>}
                    </button>;
                })}
            </p>
            {display.translation && line.translation &&
                <p className="phrase-para__translation">{line.translation}</p>}
        </div>
    </div>;
}
