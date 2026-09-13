import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { connect } from 'react-redux';
import type { ThunkDispatch } from 'redux-thunk';

import { WordListMembershipProvider } from '@root/apps/book-browser/components/GlossaryEntities/WordListMembershipContext';
import classNames from '@root/utilities/ClassNames';

import { SentenceActions } from '../actions';
import Apparatus from '../components/Apparatus';
import ProseParagraph from '../components/ProseParagraph';
import SavePhraseWords from '../components/SavePhraseWords';
import SectionRule from '../components/SectionRule';
import StudyBar from '../components/StudyBar';
import VerseLine from '../components/VerseLine';
import WordEntry from '../components/WordEntry';
import WordIndex from '../components/WordIndex';
import type { ApparatusTab } from '../components/Apparatus._types';
import type { RootReducer } from '../reducers';
import type { IEventProps, IProps } from './PhraseReader._types';

import './PhraseReader.scss';

/** Below this the apparatus is a sheet over the text rather than a column beside it. */
const NarrowBreakpoint = '(max-width: 1080px)';

const isNarrow = () => typeof window === 'object' && window.matchMedia(NarrowBreakpoint).matches;

export function PhraseReader(props: IProps) {
    const {
        display,
        fragments,
        lines,
        selection,
        sentence,
        trail,

        onFragmentSelect,
        onToggleDisplay,
    } = props;

    const [ tab, setTab ] = useState<ApparatusTab>('index');
    // Narrow screens only: the sheet's state lives here because the text must make room
    // for it, and scrolling a word into view must know how much of the screen is left.
    const [ isSheetOpen, setIsSheetOpen ] = useState(false);
    const _textRef = useRef<HTMLDivElement>(null);

    const fragmentsById = useMemo(
        () => new Map(fragments.filter((f) => f.id).map((f) => [ f.id, f ])),
        [ fragments ],
    );

    /** The words that can be opened, in reading order. */
    const words = useMemo(
        () => fragments.filter((f) => f.id && f.lexicalEntryId),
        [ fragments ],
    );

    /**
     * Fragment id → the ordinal of its line. Paragraph numbers are not ordinal: the Moria
     * gate inscription numbers its two lines 10 and 20.
     */
    const lineNumbers = useMemo(() => {
        const map = new Map<number, number>();
        for (const line of lines.lines) {
            for (const token of line.latin) {
                if (token.fragmentId) {
                    map.set(token.fragmentId, line.n);
                }
            }
        }
        return map;
    }, [ lines ]);

    const trailSet = useMemo(() => new Set(trail), [ trail ]);

    /** Distinct entries, for the membership check and for keeping the whole phrase. */
    const lexicalEntryIds = useMemo(
        () => Array.from(new Set(words.map((word) => word.lexicalEntryId))),
        [ words ],
    );

    // The heart and the "keep these words" button are meaningless to a signed-out reader,
    // so they are not offered rather than offered and then refused.
    const isSignedIn = typeof document === 'object' &&
        ! [ null, '', '0' ].includes(document.body.getAttribute('data-account-id'));

    const activeLine = selection ? lineNumbers.get(selection.id) : null;

    const _onSelectFragment = useCallback((fragmentId: number, followText = false) => {
        const fragment = fragmentsById.get(fragmentId) || null;
        onFragmentSelect(fragment, sentence?.id);
        if (fragment) {
            setTab('entry');
        }

        // On a narrow screen choosing a word opens the sheet over the text, so the word
        // has to be brought back into what remains -- even when it was clicked in place.
        const narrow = isNarrow();
        if (fragment && narrow) {
            setIsSheetOpen(true);
        }
        if (followText || (fragment && narrow)) {
            // After the sheet's own transition, so its height is the open height.
            window.setTimeout(() => _scrollToWord(fragmentId), narrow ? 300 : 0);
        }
    }, [ fragmentsById, onFragmentSelect, sentence ]);

    /** Selecting from the panel or the keyboard follows the text; clicking in it does not. */
    const _onSelectFromPanel = useCallback(
        (fragmentId: number) => _onSelectFragment(fragmentId, true),
        [ _onSelectFragment ],
    );

    const _scrollToWord = (fragmentId: number) => {
        const root = _textRef.current;
        if (! root) {
            return;
        }

        const word = root.querySelector<HTMLElement>(
            `.phrase-line__latin [data-fragment="${fragmentId}"], .phrase-para [data-fragment="${fragmentId}"]`,
        );
        if (! word) {
            return;
        }

        // The study bar covers the top; on a narrow screen the sheet covers the bottom.
        // What is left between them is the only part of the page the reader can see.
        const top = _studyBarHeight() + 16;
        const bottom = window.innerHeight - _sheetHeight() - 16;

        const rect = word.getBoundingClientRect();
        if (rect.top >= top && rect.bottom <= bottom) {
            return;
        }

        // scrollIntoView centres on the whole viewport, which on a narrow screen means
        // centring behind the sheet. Scroll by hand to the middle of the visible strip.
        const delta = rect.top - (top + (bottom - top) / 2 - rect.height / 2);
        window.scrollBy({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            top: delta,
        });
    };

    const _studyBarHeight = () =>
        document.querySelector('.phrase-studybar')?.getBoundingClientRect().height || 0;

    const _sheetHeight = () => {
        const sheet = document.querySelector('.phrase-apparatus');
        if (! sheet || getComputedStyle(sheet).position !== 'fixed') {
            return 0;
        }
        return sheet.getBoundingClientRect().height;
    };

    useEffect(() => {
        const __onKeyUp = (ev: KeyboardEvent) => {
            if (! selection) {
                return;
            }
            switch (ev.key) {
                case 'ArrowLeft':
                    if (selection.previousFragmentId) {
                        _onSelectFragment(selection.previousFragmentId, true);
                    }
                    break;
                case 'ArrowRight':
                    if (selection.nextFragmentId) {
                        _onSelectFragment(selection.nextFragmentId, true);
                    }
                    break;
                case 'Escape':
                    onFragmentSelect(null, sentence?.id);
                    setTab('index');
                    break;
            }
        };

        document.addEventListener('keyup', __onKeyUp);
        return () => document.removeEventListener('keyup', __onKeyUp);
    }, [ selection, _onSelectFragment, onFragmentSelect, sentence ]);

    const unopened = words.filter((word) => ! trailSet.has(word.id));
    const position = selection ? words.findIndex((word) => word.id === selection.id) + 1 : 0;
    const nextUnopened = selection
        ? (unopened.find((word) => words.indexOf(word) > position - 1) || unopened[0])
        : unopened[0];

    return <WordListMembershipProvider lexicalEntryIds={isSignedIn ? lexicalEntryIds : []}>
        <div className={classNames('phrase-reader', { 'phrase-reader--sheet-open': isSheetOpen })}>
            <StudyBar
                action={isSignedIn
                    ? <SavePhraseWords lexicalEntryIds={lexicalEntryIds} phraseName={sentence?.name || 'this phrase'} />
                    : null}
                display={display}
                hasTranslations={lines.hasTranslations}
                isSignedIn={isSignedIn}
                onToggle={onToggleDisplay}
                openedCount={trailSet.size}
                sentenceId={sentence?.id || 0}
                totalCount={words.length}
            />

            <div className="phrase-reader__body">
                <div className="phrase-reader__text" ref={_textRef}>
                    {lines.lines.map((line, i) => {
                        if (line.kind === 'rule') {
                            return <SectionRule key={`rule-${i}`} />;
                        }

                        // Verse or prose is settled for the whole phrase by the shape of its
                        // paragraphs; a phrase never mixes the two.
                        const Line = line.kind === 'prose' ? ProseParagraph : VerseLine;
                        return <Line key={line.paragraphNumber}
                            display={display}
                            fragments={fragmentsById}
                            isActive={activeLine === line.n}
                            line={line}
                            onSelectFragment={_onSelectFragment}
                            selectedFragmentId={selection?.id || 0}
                            trail={trailSet}
                        />;
                    })}
                </div>

                <Apparatus
                    tab={tab}
                    onTabChange={setTab}
                    isOpen={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    handleText={selection
                        ? `${selection.fragment}${selection.gloss ? ` — ${selection.gloss}` : ''}`
                        : `Word by word — ${words.length} words to open`}
                    index={<WordIndex
                        lineNumbers={lineNumbers}
                        onSelectFragment={_onSelectFromPanel}
                        selectedFragmentId={selection?.id || 0}
                        shape={lines.shape}
                        trail={trailSet}
                        words={words}
                    />}
                    entry={selection
                        ? <WordEntry
                            fragment={selection}
                            lineNumber={lineNumbers.get(selection.id)}
                            onSelectFragment={_onSelectFromPanel}
                            position={position}
                            shape={lines.shape}
                            totalCount={words.length}
                            unopenedCount={unopened.length}
                            unopenedFragmentId={nextUnopened?.id || 0}
                        />
                        : <p className="phrase-apparatus__empty">
                            No word selected. Pick one from the text, or from the word-by-word list.
                        </p>}
                />
            </div>
        </div>
    </WordListMembershipProvider>;
}

const mapStateToProps = (state: RootReducer): IProps => ({
    display: state.display,
    fragments: state.fragments,
    lines: state.lines,
    selection: state.selection,
    sentence: state.sentence,
    trail: state.trail,
});

const mapDispatchToProps = (dispatch: ThunkDispatch<any, any, any>): IEventProps => ({
    onFragmentSelect: (fragment, sentenceId) => {
        const actions = new SentenceActions();
        dispatch(actions.selectFragment(fragment || null, sentenceId));
    },
    onToggleDisplay: (mode) => {
        const actions = new SentenceActions();
        dispatch(actions.toggleDisplay(mode));
    },
});

export default connect(mapStateToProps, mapDispatchToProps)(PhraseReader);
