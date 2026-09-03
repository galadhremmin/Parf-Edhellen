import {
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import type { ChangeEvent, KeyboardEvent, MouseEvent } from 'react';

import { fireEvent, fireEventAsync } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import classNames from '@root/utilities/ClassNames';
import { excludeProps } from '@root/utilities/func/props';
import {
    DefaultPlaceholder,
    getSuggestion,
    randomSuggestionIndex,
} from './SearchQueryInput._suggestions';
import type { IProps } from './SearchQueryInput._types';

import './SearchQueryInput.scss';

/** Delay before the field offers its first suggestion. */
const FirstSuggestionDelayMs = 1500;
/** Time a suggestion rests in the field before it is erased again. */
const SuggestionDurationMs = 3500;
const TypeSpeedMs = 45;
const TypeJitterMs = 45;
const EraseSpeedMs = 22;

type Phase = 'erasing' | 'holding' | 'typing';

const prefersReducedMotion = () => typeof window !== 'undefined' &&
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const commonPrefixLength = (a: string, b: string) => {
    let length = 0;
    while (length < a.length && length < b.length && a[length] === b[length]) {
        length += 1;
    }
    return length;
};

function SearchQueryInput(props: IProps) {
    const {
        className,
        loading = false,
        name,
        onChange,
        onSearchResultNavigate,
        onSuggestionSelect,
        showEnterHint = false,
        value = '',
    } = props;

    const [ placeholder, setPlaceholder ] = useState(DefaultPlaceholder);
    const [ suggestion, setSuggestion ] = useState<string | null>(null);

    const inputRef = useRef<HTMLInputElement>(null);
    // Survives the animation being torn down and set up again, so that clearing the field
    // continues where the previous run left off rather than repeating itself.
    const indexRef = useRef(randomSuggestionIndex());

    const isEmpty = value.length === 0;
    const hasSuggestion = suggestion !== null && typeof onSuggestionSelect === 'function';

    // The suggestions are an invitation to start searching, and thus only relevant while the
    // field is empty.
    useEffect(() => {
        if (! isEmpty) {
            setPlaceholder(DefaultPlaceholder);
            setSuggestion(null);
            return undefined;
        }

        if (prefersReducedMotion()) {
            // Offer a suggestion, but without the motion that advertises it.
            const { query, text } = getSuggestion(indexRef.current);
            setPlaceholder(text);
            setSuggestion(query);
            return undefined;
        }

        let timer: ReturnType<typeof setTimeout> | null = null;
        let phase: Phase = 'holding';
        let text = DefaultPlaceholder;
        let target = DefaultPlaceholder;
        let keepLength = 0;

        const setText = (nextText: string) => {
            text = nextText;
            setPlaceholder(nextText);
        };

        const step = () => {
            switch (phase) {
                case 'erasing':
                    if (text.length > keepLength) {
                        setText(text.slice(0, -1));
                        timer = setTimeout(step, EraseSpeedMs);
                    } else {
                        phase = 'typing';
                        setSuggestion(getSuggestion(indexRef.current).query);
                        timer = setTimeout(step, TypeSpeedMs);
                    }
                    break;

                case 'typing':
                    if (text.length < target.length) {
                        setText(target.slice(0, text.length + 1));
                        timer = setTimeout(step, TypeSpeedMs + Math.random() * TypeJitterMs);
                    } else {
                        indexRef.current += 1;
                        phase = 'holding';
                        timer = setTimeout(step, SuggestionDurationMs);
                    }
                    break;

                case 'holding':
                    target = getSuggestion(indexRef.current).text;
                    // Consecutive examples share the "Try “" prefix, which therefore stays put.
                    keepLength = commonPrefixLength(text, target);
                    phase = 'erasing';
                    timer = setTimeout(step, EraseSpeedMs);
                    break;
            }
        };

        timer = setTimeout(step, FirstSuggestionDelayMs);
        return () => {
            if (timer) {
                clearTimeout(timer);
            }
        };
    }, [ isEmpty ]);

    /**
     * Accepts the suggestion currently advertised by the field, and moves on to the next one so
     * that the field never repeats itself.
     */
    const selectSuggestion = useCallback(() => {
        indexRef.current += 1;
        inputRef.current?.focus();
        if (onSuggestionSelect) {
            fireEvent(name || 'SearchQueryInput', onSuggestionSelect, suggestion ?? '');
        }
    }, [ name, onSuggestionSelect, suggestion ]);

    const _onChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        const { value: newValue } = ev.target;
        if (onChange && newValue !== value) {
            fireEvent(name  || 'SearchQueryInput', onChange, newValue);
        }
    }, [ name, onChange, value ]);

    const _onSuggestionClick = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        selectSuggestion();
    }, [ selectSuggestion ]);

    const _onKeyDown = useCallback((ev: KeyboardEvent<HTMLInputElement>) => {
        // Accepting the suggestion with the enter key saves the curious visitor from typing a word
        // in a language they do not know yet.
        if (ev.key === 'Enter' && isEmpty && hasSuggestion) {
            ev.preventDefault();
            selectSuggestion();
            return;
        }

        let direction = 0;
        switch (ev.key) {
            case 'ArrowUp': // up
                direction = -1;
                break;
            case 'Enter': // enter
            case 'ArrowDown': // down
                direction = +1;
                break;
        }

        if (! direction || typeof onSearchResultNavigate !== 'function') {
            return;
        }

        void fireEventAsync(name || 'SearchQueryInput', onSearchResultNavigate, direction);
    }, [ hasSuggestion, isEmpty, name, onSearchResultNavigate, selectSuggestion ]);

    const componentProps = excludeProps(props, [
        'className', 'loading', 'onChange', 'onSearchResultNavigate', 'onSuggestionSelect',
        'showEnterHint', 'value',
    ]);

    const idle = isEmpty && !loading;
    const showSuggestion = idle && !showEnterHint && hasSuggestion;
    const icon = loading ? 'refresh' : 'search';

    return <div className="input-group input-group-lg">
        <span className={classNames('input-group-text', 'SearchQueryInput--icon', { 'is-idle': idle })}>
            <TextIcon icon={icon} className={loading ? 'loading' : ''} />
        </span>
        <input accessKey="s"
            autoCapitalize="off"
            autoComplete="off"
            className={classNames('form-control', 'SearchQueryInput--field', className || '', {
                disabled: loading,
            })}
            onChange={_onChange}
            onKeyDown={_onKeyDown}
            placeholder={placeholder}
            ref={inputRef}
            type="search"
            value={value}
            {...componentProps}
        />
        {showSuggestion && <button className="input-group-text SearchQueryInput--suggestion"
            onClick={_onSuggestionClick}
            tabIndex={-1}
            title={`Look up “${suggestion}”`}
            type="button"
        >
            Try it
        </button>}
        {showEnterHint && <span className="input-group-text SearchQueryInput--enter-hint">
            Enter &crarr;
        </span>}
    </div>;
}

export default SearchQueryInput;
