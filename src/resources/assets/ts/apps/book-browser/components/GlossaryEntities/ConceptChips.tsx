import { useCallback, useState } from 'react';
import type { MouseEvent } from 'react';

import type { IRelatedConcept } from '@root/connectors/backend/IBookApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import type { IProps } from './ConceptChips._types';

import './ConceptChips.scss';

const DefaultInitialCount = 12;

/**
 * Concepts a reader can move to from what they searched: the kinds of it (oak, beech, mallorn for "tree") or what it
 * is itself a kind of (tree, woody plant, plant for "birch"). Each one is a word of its own in the dictionary, so each
 * opens the dictionary on it — through the glossary, without reloading the page, while remaining an ordinary link for
 * anyone who wants it in a new tab.
 */
function ConceptChips(props: IProps) {
    const {
        concepts,
        globalEvents,
        heading,
        initialCount = DefaultInitialCount,
    } = props;

    const [ showAll, setShowAll ] = useState<boolean>(false);

    const _onConceptOpen = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
        // leave the browser's own ways of opening a link alone
        if (ev.button !== 0 || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) {
            return;
        }

        ev.preventDefault();

        const concept = (ev.currentTarget as HTMLAnchorElement).dataset.concept;
        globalEvents?.fire(globalEvents.loadReference, {
            languageShortName: null as string,
            normalizedWord: concept,
            word: concept,
        });
    }, [globalEvents]);

    if (! concepts?.length) {
        return null;
    }

    const visible = showAll ? concepts : concepts.slice(0, initialCount);
    const hidden = concepts.length - visible.length;

    return <nav className="ConceptChips" aria-label={heading}>
        <span className="ed-label ConceptChips--label">{heading}</span>
        <ul className="ConceptChips--list">
            {visible.map((concept: IRelatedConcept) => <li key={concept.label}>
                <a
                    className="ConceptChips--concept"
                    data-concept={concept.label}
                    href={`/w/${encodeURIComponent(concept.label)}`}
                    onClick={_onConceptOpen}
                >
                    {concept.label}
                    <span className="ConceptChips--count">{concept.entries}</span>
                </a>
            </li>)}
            {hidden > 0 && <li>
                <button className="ConceptChips--more" onClick={() => setShowAll(true)} type="button">
                    {hidden} more…
                </button>
            </li>}
        </ul>
    </nav>;
}

export default withPropInjection(ConceptChips, {
    globalEvents: DI.GlobalEvents,
});
