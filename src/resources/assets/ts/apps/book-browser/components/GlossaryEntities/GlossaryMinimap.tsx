import { useCallback, useEffect, useRef, useState } from 'react';

import type { IProps } from './GlossaryMinimap._types';
import TextIcon from '@root/components/TextIcon';

import './GlossaryMinimap.scss';

const MAX_WORDS = 10;

/**
 * Breakpoint (in px) above which the minimap is always visible in the whitespace.
 * Below this but above `lg`, it renders as a foldable panel.
 * Must stay in sync with $screen-xxxl-width in _variables.scss.
 */
const ALWAYS_VISIBLE_WIDTH = 2200;

/** Minimum height (px) before the minimap is hidden to avoid a tiny sliver. */
const MIN_VISIBLE_HEIGHT = 120;

function GlossaryMinimap({ languages, sections }: IProps) {
    const [ activeLanguageId, setActiveLanguageId ] = useState<number | null>(null);
    const [ isOpen, setIsOpen ] = useState(false);
    const observerRef = useRef<IntersectionObserver | null>(null);
    const scrollRef = useRef<HTMLDivElement>(null);
    const rafRef = useRef<number>(0);

    // Set up IntersectionObserver for scroll spy on language sections
    useEffect(() => {
        const languageElements = languages
            .map((lang) => document.getElementById(`glossary-lang-${lang.id}`))
            .filter(Boolean);

        if (languageElements.length === 0) {
            return;
        }

        const visibleSections = new Map<string, IntersectionObserverEntry>();

        observerRef.current = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        visibleSections.set(entry.target.id, entry);
                    } else {
                        visibleSections.delete(entry.target.id);
                    }
                }

                let topmost: IntersectionObserverEntry | null = null;
                for (const entry of visibleSections.values()) {
                    if (topmost === null || entry.boundingClientRect.top < topmost.boundingClientRect.top) {
                        topmost = entry;
                    }
                }

                if (topmost) {
                    const id = topmost.target.id.replace('glossary-lang-', '');
                    setActiveLanguageId(Number(id));
                }
            },
            {
                rootMargin: '-10% 0px -60% 0px',
                threshold: 0,
            },
        );

        for (const el of languageElements) {
            observerRef.current.observe(el);
        }

        return () => {
            observerRef.current?.disconnect();
        };
    }, [languages]);

    // Shrink the minimap when the page footer scrolls into view
    useEffect(() => {
        const footer = document.querySelector<HTMLElement>('body > footer');
        if (!footer || !scrollRef.current) {
            return;
        }

        const updateHeight = () => {
            const el = scrollRef.current;
            if (!el) {
                return;
            }

            const footerRect = footer.getBoundingClientRect();
            const scrollRect = el.getBoundingClientRect();

            if (footerRect.top < window.innerHeight && footerRect.top > scrollRect.top) {
                // Footer is visible — shrink to stop above it
                const available = footerRect.top - scrollRect.top - 16; // 16px gap
                if (available < MIN_VISIBLE_HEIGHT) {
                    el.style.maxHeight = '0';
                    el.style.overflow = 'hidden';
                } else {
                    el.style.maxHeight = `${available}px`;
                    el.style.overflow = '';
                }
            } else {
                // Footer off-screen — use default max-height from CSS
                el.style.maxHeight = '';
                el.style.overflow = '';
            }

            rafRef.current = 0;
        };

        const onScroll = () => {
            if (rafRef.current === 0) {
                rafRef.current = requestAnimationFrame(updateHeight);
            }
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        updateHeight();

        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
            if (rafRef.current) {
                cancelAnimationFrame(rafRef.current);
            }
        };
    }, []);

    const closeIfPanel = useCallback(() => {
        if (window.innerWidth < ALWAYS_VISIBLE_WIDTH) {
            setIsOpen(false);
        }
    }, []);

    const scrollToLanguage = useCallback((languageId: number) => {
        const el = document.getElementById(`glossary-lang-${languageId}`);
        el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        closeIfPanel();
    }, [closeIfPanel]);

    const scrollToWord = useCallback((entryId: number) => {
        const el = document.getElementById(`lexical-entry-block-${entryId}`);
        el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        closeIfPanel();
    }, [closeIfPanel]);

    const togglePanel = useCallback(() => {
        setIsOpen((prev) => !prev);
    }, []);

    return <div className={`glossary-minimap${isOpen ? ' glossary-minimap--open' : ''}`}>
        <button
            className="glossary-minimap__toggle"
            onClick={togglePanel}
            aria-label={isOpen ? 'Close index' : 'Open index'}
            title={isOpen ? 'Close index' : 'Open index'}
        >
            <span className="glossary-minimap__toggle-label">
                <TextIcon icon="book" />
                &nbsp;
                Index
            </span>
            <span className="glossary-minimap__toggle-chevron" />
        </button>
        <nav className="glossary-minimap__panel" aria-label="Glossary outline">
            <div className="glossary-minimap__scroll" ref={scrollRef}>
                {languages.map((lang) => {
                    const entries = sections[lang.id] || [];
                    const isActive = activeLanguageId === lang.id;
                    const visibleEntries = entries.slice(0, MAX_WORDS);
                    const remaining = entries.length - MAX_WORDS;

                    return <div
                        key={lang.id}
                        className={`glossary-minimap__section${isActive ? ' glossary-minimap__section--active' : ''}`}
                    >
                        <a
                            className="glossary-minimap__language"
                            onClick={() => scrollToLanguage(lang.id)}
                            role="button"
                            tabIndex={0}
                        >
                            {lang.name}
                            {!!lang.isUnusual && <span className="glossary-minimap__language-unusual"> †</span>}
                        </a>
                        <ul className="glossary-minimap__words">
                            {visibleEntries.map((entry) => <li key={entry.id}>
                                <a
                                    className="glossary-minimap__word"
                                    onClick={() => scrollToWord(entry.id)}
                                    role="button"
                                    tabIndex={0}
                                >
                                    {entry.word}
                                </a>
                            </li>)}
                            {remaining > 0 && <li className="glossary-minimap__more">
                                …and {remaining} more
                            </li>}
                        </ul>
                    </div>;
                })}
            </div>
        </nav>
    </div>;
}

export default GlossaryMinimap;
