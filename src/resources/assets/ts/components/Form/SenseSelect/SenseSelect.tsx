import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';

import type { IConceptSuggestion, ISenseSuggestion } from '@root/connectors/backend/ISenseApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import debounce from '@root/utilities/func/debounce';

import { fireEvent } from '../../Component';
import type { IProps, ISenseSelection } from './SenseSelect._types';

import './SenseSelect.scss';

const SuggestionDelay = 300;

/**
 * The sense of a lexical entry: the wording readers see, and — when the taxonomy has a word for it — what that wording
 * means. The wording is free text, as it has always been, but both the wordings already in use and the meanings behind
 * them are offered as you type, so that entries meaning the same thing come to be worded and grouped together. Once a
 * meaning is chosen, the wordings offered are the ones that already mean it.
 */
function SenseSelect(props: IProps) {
    const {
        concept,
        conceptId,
        id,
        name = 'sense',
        onChange,
        required,
        sense,
        senseApi,
        senseId,
    } = props;

    const [ concepts, setConcepts ] = useState<IConceptSuggestion[]>([]);
    const [ senses, setSenses ] = useState<ISenseSuggestion[]>([]);
    const [ open, setOpen ] = useState<boolean>(false);
    const latestQuery = useRef<string>(null);

    const _select = useCallback((selection: ISenseSelection) => {
        void fireEvent(name, onChange, selection);
    }, [name, onChange]);

    const _suggest = useMemo(() => debounce(SuggestionDelay, async (args: { meaning: number, text: string }) => {
        const { meaning, text } = args;
        const question = `${meaning ?? ''}:${text}`;
        latestQuery.current = question;

        if (text.length < 1 && ! meaning) {
            setConcepts([]);
            setSenses([]);

            return;
        }

        try {
            const suggestions = await senseApi.find(text, meaning);

            // an earlier request can arrive after a later one; only the newest answer is still the question
            if (latestQuery.current === question) {
                setConcepts(suggestions.concepts);
                setSenses(suggestions.senses);
            }
        } catch (error) {
            console.warn(error);
        }
    }), [senseApi]);

    useEffect(() => {
        if (open) {
            void _suggest({ meaning: conceptId, text: sense });
        }
    }, [sense, conceptId, open, _suggest]);

    const _onSenseChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        setOpen(true);
        // rewording it is no longer the sense that was picked, but it can still mean what was chosen
        _select({ concept, conceptId, sense: ev.target.value });
    }, [concept, conceptId, _select]);

    const _onConceptSelect = useCallback((suggestion: IConceptSuggestion) => {
        _select({ concept: suggestion, conceptId: suggestion.id, sense, senseId });
    }, [sense, senseId, _select]);

    const _onSenseSelect = useCallback((suggestion: ISenseSuggestion) => {
        setOpen(false);
        _select({ concept, conceptId, sense: suggestion.sense, senseId: suggestion.senseId });
    }, [concept, conceptId, _select]);

    const _onConceptRemove = useCallback(() => {
        _select({ sense, senseId });
    }, [sense, senseId, _select]);

    const showSuggestions = open && (senses.length > 0 || concepts.length > 0);

    return <div className="SenseSelect">
        <input
            autoComplete="off"
            className="form-control"
            id={id}
            name={name}
            onBlur={() => window.setTimeout(() => setOpen(false), 150)}
            onChange={_onSenseChange}
            onFocus={() => setOpen(true)}
            placeholder={concept ? `How others word "${concept.label}"…` : 'What the word means, in English'}
            required={required}
            type="text"
            value={sense}
        />

        {showSuggestions && <div className="SenseSelect--suggestions">
            {senses.length > 0 && <>
                <span className="SenseSelect--heading">
                    {conceptId ? 'Wordings that already mean this' : 'Wordings already in use'}
                </span>
                <ul className="SenseSelect--list">
                    {senses.map((suggestion: ISenseSuggestion) => <li key={suggestion.senseId}>
                        <button onClick={() => _onSenseSelect(suggestion)} type="button">
                            <span className="SenseSelect--meaning">{suggestion.sense}</span>
                            <span className="SenseSelect--note">{suggestion.entries} entries</span>
                        </button>
                    </li>)}
                </ul>
            </>}
            {concepts.length > 0 && ! concept && <>
                <span className="SenseSelect--heading">Meanings</span>
                <ul className="SenseSelect--list">
                    {concepts.map((suggestion: IConceptSuggestion) => <li key={suggestion.id}>
                        <button onClick={() => _onConceptSelect(suggestion)} type="button">
                            <span className="SenseSelect--meaning">{suggestion.label}</span>
                            {suggestion.synonyms.length > 0 &&
                                <span className="SenseSelect--synonyms">also {suggestion.synonyms.join(', ')}</span>}
                            <span className="SenseSelect--definition">{suggestion.definition}</span>
                            <span className="SenseSelect--note">
                                {suggestion.lineage.length > 0 && <>a kind of {suggestion.lineage.join(' ‹ ')} · </>}
                                {suggestion.entries} entries
                            </span>
                        </button>
                    </li>)}
                </ul>
            </>}
        </div>}

        {concept && <div className="SenseSelect--chosen">
            <span className="SenseSelect--meaning">{concept.label}</span>
            <span className="SenseSelect--definition">{concept.definition}</span>
            <span className="SenseSelect--note">
                shared by every entry worded the same way
            </span>
            <button className="SenseSelect--remove" onClick={_onConceptRemove} type="button">Remove</button>
        </div>}
    </div>;
}

export default withPropInjection(SenseSelect, {
    senseApi: DI.SenseApi,
});
