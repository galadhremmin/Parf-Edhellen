import { useEffect, useState } from 'react';

import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import Spinner from '@root/components/Spinner';

import type { IConceptSuggestion } from '@root/connectors/backend/ISenseApi';
import type { IMeaningSearchProps } from './SenseReviewer._types';

/** Long enough that a search is worth making, short enough that "oak" still finds something. */
const MinimumQueryLength = 2;

const DebounceInMilliseconds = 250;

/**
 * For when no candidate fits: the taxonomy searched by hand. The box starts empty on every sense — seeded with the
 * headword it answered "this" with "thistle", which the search only matched because one word begins the other.
 */
const MeaningSearch = (props: IMeaningSearchProps) => {
    const { disabled, headword, onChoose } = props;
    const senseApi = props.senseApi ?? resolve(DI.SenseApi);

    const [ query, setQuery ] = useState<string>('');
    const [ meanings, setMeanings ] = useState<IConceptSuggestion[]>([]);
    const [ searching, setSearching ] = useState<boolean>(false);

    // a new sense is a new search: what was typed for the last one means nothing here
    useEffect(() => setQuery(''), [ headword ]);

    useEffect(() => {
        const text = query.trim();
        if (text.length < MinimumQueryLength) {
            setMeanings([]);
            return undefined;
        }

        let abandoned = false;
        setSearching(true);
        const timer = setTimeout(() => {
            senseApi.find(text)
                .then((response) => {
                    if (! abandoned) {
                        setMeanings(response.concepts ?? []);
                    }
                })
                .catch(() => {
                    if (! abandoned) {
                        setMeanings([]);
                    }
                })
                .finally(() => {
                    if (! abandoned) {
                        setSearching(false);
                    }
                });
        }, DebounceInMilliseconds);

        return () => {
            abandoned = true;
            clearTimeout(timer);
        };
    }, [ query, senseApi ]);

    const named = meanings.filter((meaning) => meaning.named);
    const loose = meanings.filter((meaning) => ! meaning.named);

    return <section className="SenseReviewer--search">
        <label className="form-label" htmlFor="sense-review-search">
            No candidate fits? Search the taxonomy
        </label>
        <input type="search" className="form-control" id="sense-review-search" value={query} disabled={disabled}
            placeholder={`a plain English word for what “${headword}” means`}
            onChange={(ev) => setQuery(ev.target.value)} />

        {searching && <p className="text-muted mt-2 mb-0"><Spinner /> Searching…</p>}

        {! searching && named.length === 0 && loose.length > 0 && <p className="text-muted small mt-2 mb-0">
            Nothing in the taxonomy is called “{query.trim()}”. These only begin with it, so read them before
            taking one.
        </p>}

        {! searching && meanings.length > 0 && <ul className="SenseReviewer--search-results list-unstyled mt-2 mb-0">
            {meanings.map((meaning) => <li key={meaning.id} className="SenseReviewer--search-result">
                <div>
                    <strong>{meaning.label}</strong>
                    {meaning.lineage.length > 0 && <span className="text-muted small">
                        {' '}— a kind of {meaning.lineage.join(' › ')}
                    </span>}
                    {meaning.definition && <div className="small text-muted">{meaning.definition}</div>}
                    <div className="small text-muted">{meaning.entries} entries beneath it</div>
                </div>
                <button type="button" className="btn btn-sm btn-outline-primary" disabled={disabled}
                    onClick={() => onChoose(meaning.id)}>
                    Means this
                </button>
            </li>)}
        </ul>}

        {! searching && query.trim().length >= MinimumQueryLength && meanings.length === 0 &&
            <p className="text-muted small mt-2 mb-0">The taxonomy knows no meaning by that name.</p>}
    </section>;
};

export default MeaningSearch;
