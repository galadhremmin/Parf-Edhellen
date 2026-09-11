import { useCallback, useEffect, useRef, useState } from 'react';
import type { FocusEvent } from 'react';

import { CommonPaths } from '@root/config';

import type { IProps } from './MissingEnglishNotice._types';

/**
 * Stands in for the English toggle on a phrase nobody translated.
 *
 * Twenty-six of the corpus's sixty-nine phrases have no translation recorded, and a
 * greyed-out control that says nothing reads as a fault in the page rather than a gap in
 * the book. So it explains itself, and offers the reader the way to fill the gap.
 *
 * It is `aria-disabled` rather than `disabled`: a genuinely disabled control fires no
 * mouse events in most browsers, so it could be neither hovered nor focused, and the
 * explanation would be unreachable by exactly the people who need it.
 */
export default function MissingEnglishNotice(props: IProps) {
    const {
        isSignedIn,
        sentenceId,
    } = props;

    const [ isOpen, setIsOpen ] = useState(false);
    const _rootRef = useRef<HTMLSpanElement>(null);

    const _onClick = useCallback(() => setIsOpen((open) => ! open), []);

    /**
     * Focus moving from the button into the notice's own link is not focus leaving the
     * notice. Closing on any blur would unmount the link on mousedown, before its click
     * ever landed -- which made the call to action unclickable.
     */
    const _onBlur = useCallback((ev: FocusEvent<HTMLElement>) => {
        if (! _rootRef.current?.contains(ev.relatedTarget as Node)) {
            setIsOpen(false);
        }
    }, []);

    useEffect(() => {
        if (! isOpen) {
            return;
        }

        const __onKeyUp = (ev: KeyboardEvent) => {
            if (ev.key === 'Escape') {
                setIsOpen(false);
            }
        };
        const __onPointerDown = (ev: Event) => {
            if (! _rootRef.current?.contains(ev.target as Node)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('keyup', __onKeyUp);
        document.addEventListener('pointerdown', __onPointerDown);
        return () => {
            document.removeEventListener('keyup', __onKeyUp);
            document.removeEventListener('pointerdown', __onPointerDown);
        };
    }, [ isOpen ]);

    const contributeUrl = `${CommonPaths.contributions.sentence}?entity_id=${sentenceId}`;
    const returnTo = typeof window === 'object' ? window.location.pathname : '';

    return <span className="phrase-notice"
        ref={_rootRef}
        onMouseEnter={() => setIsOpen(true)}
        onMouseLeave={() => setIsOpen(false)}
        onFocus={() => setIsOpen(true)}
        onBlur={_onBlur}>

        <button type="button"
            className="phrase-toggle"
            aria-disabled="true"
            aria-expanded={isOpen}
            aria-describedby={`phrase-notice-${sentenceId}`}
            onClick={_onClick}>
            English
        </button>

        <span className="phrase-notice__body"
            id={`phrase-notice-${sentenceId}`}
            role="tooltip"
            hidden={! isOpen}>
            <span className="phrase-notice__title ed-label">No English for this phrase</span>
            <span className="phrase-notice__text">
                Nobody recorded a translation when this phrase was added. The elvish is still
                here, word by word — only the English line is missing.
            </span>
            {isSignedIn
                ? <a className="phrase-notice__cta" href={contributeUrl}>
                    Propose a translation &#8594;
                </a>
                : <a className="phrase-notice__cta" href={`/login?redirect=${encodeURIComponent(returnTo)}`}>
                    Sign in to add one &#8594;
                </a>}
        </span>
    </span>;
}
