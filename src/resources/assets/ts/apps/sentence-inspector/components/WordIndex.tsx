import { type ReactNode, useMemo, useState } from 'react';

import classNames from '@root/utilities/ClassNames';

import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import type { IProps, WordFilter } from './WordIndex._types';

const MutationsGroup = 'Mutations';

/**
 * Words past which the list needs a way in. Twenty-five rows can be read; the King's
 * Letter has eighty-four, and scrolling that in search of the interesting ones is not
 * exploring.
 */
export const FILTER_AT = 30;

const Filters: { key: WordFilter; label: string }[] = [
    { key: 'all', label: 'All' },
    { key: 'note', label: 'With notes' },
    { key: 'changed', label: 'Changed forms' },
    { key: 'unopened', label: 'Unopened' },
];

/**
 * The panel's resting state: every word there is to open, with what is known about each.
 *
 * The panel used to open empty, so the reader could not see how much there was to explore
 * or which words were worth a click. This is the same data `_annotations.blade.php` has
 * always assembled for the front page and the phrase page never used.
 */
export default function WordIndex(props: IProps) {
    const {
        lineNumbers,
        onSelectFragment,
        selectedFragmentId,
        shape,
        trail,
        words,
    } = props;

    const [ filter, setFilter ] = useState<WordFilter>('all');

    const unit = shape === 'prose' ? '¶ ' : 'l. ';
    const isLong = words.length >= FILTER_AT;

    const changed = words.filter((word) => word.isChanged).length;
    const annotated = words.filter((word) => word.lexicalEntryInflections.length > 0).length;
    const noted = words.filter((word) => word.hasNote).length;

    // Rendered only when it has something to say: a summary that is empty on two phrases
    // in five teaches the reader to stop reading it.
    const summary = ([
        [changed, 'words are written differently from the entry they link to'],
        [annotated, 'carry an inflection recorded by an editor'],
        [noted, 'carry a note written by an editor'],
    ] as [number, string][]).filter(([count]) => count > 0);

    const matches = (word: IFragmentsReducerState) => {
        switch (filter) {
            case 'note':
                return word.hasNote;
            case 'changed':
                return word.isChanged;
            case 'unopened':
                return ! trail.has(word.id);
            default:
                return true;
        }
    };

    /** Grouped by the line they stand in, so a long list keeps its bearings. */
    const groups = useMemo(() => {
        const byLine = new Map<number, IFragmentsReducerState[]>();
        for (const word of words) {
            const line = lineNumbers.get(word.id) || 0;
            const existing = byLine.get(line);
            if (existing) {
                existing.push(word);
            } else {
                byLine.set(line, [ word ]);
            }
        }
        return Array.from(byLine.entries());
    }, [ lineNumbers, words ]);

    const visible = words.filter(matches).length;

    const renderMarks = (word: IFragmentsReducerState) => {
        const marks: ReactNode[] = [];
        if (word.hasNote) {
            marks.push(<span key="note" className="phrase-mark phrase-mark--gild">note</span>);
        }
        for (const inflection of word.lexicalEntryInflections) {
            if (! inflection.inflection) {
                continue;
            }
            marks.push(<span key={inflection.inflection.id}
                className={classNames('phrase-mark', {
                    'phrase-mark--gild': inflection.inflection.groupName === MutationsGroup,
                })}>
                {inflection.inflection.name}
            </span>);
        }
        return marks.length > 0 ? <span className="phrase-marks">{marks}</span> : null;
    };

    const renderRow = (word: IFragmentsReducerState) => <button key={word.id}
        type="button"
        hidden={! matches(word)}
        className={classNames('phrase-indexrow', {
            'is-opened': trail.has(word.id),
            'is-selected': word.id === selectedFragmentId,
        })}
        onClick={() => onSelectFragment(word.id)}>
        <span className="phrase-indexrow__form">
            {word.fragment}
            {renderMarks(word)}
        </span>
        <span className="phrase-indexrow__line">{unit}{lineNumbers.get(word.id)}</span>
        <span className="phrase-indexrow__meta">
            {word.speech && <span className="phrase-indexrow__speech">{word.speech} </span>}
            {word.gloss}
        </span>
    </button>;

    return <div className={classNames('phrase-index', { 'phrase-index--long': isLong })}>
        {isLong && <div className="phrase-index__filters">
            {Filters.map(({ key, label }) => <button key={key}
                type="button"
                className="phrase-toggle"
                aria-pressed={filter === key}
                onClick={() => setFilter(key)}>
                {label}
            </button>)}
        </div>}

        {summary.length > 0 && <section className="phrase-inthis">
            <p className="ed-label">In this phrase</p>
            {summary.map(([count, label]) => <div key={label} className="phrase-inthis__row">
                <span className="phrase-inthis__n">{count}</span>
                <span>{label}</span>
            </div>)}
        </section>}

        <p className="ed-ui phrase-index__lead">Open a word to see the entry it came from.</p>

        {isLong
            ? groups.map(([line, group]) => {
                const shown = group.filter(matches).length;
                return <div key={line} hidden={shown === 0}>
                    <div className="phrase-index__group">
                        <span className="ed-label">{unit}{line}</span>
                        <span className="ed-ui">{group.length} words</span>
                    </div>
                    {group.map(renderRow)}
                </div>;
            })
            : words.map(renderRow)}

        {visible === 0 && <p className="phrase-index__empty">
            Nothing matches this filter — every word here has been opened.
        </p>}
    </div>;
}
