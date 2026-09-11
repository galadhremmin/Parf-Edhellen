import { isLastScript } from '../reducers/DisplayReducer';
import type { DisplayMode } from '../reducers/DisplayReducer._types';
import MissingEnglishNotice from './MissingEnglishNotice';
import type { IProps } from './StudyBar._types';

import './StudyBar.scss';

const Modes: { label: string; mode: DisplayMode }[] = [
    { label: 'Tengwar', mode: 'tengwar' },
    { label: 'Latin', mode: 'latin' },
    { label: 'English', mode: 'translation' },
    { label: 'Changed forms', mode: 'changedForms' },
];

/**
 * The reader's own controls. Hiding the translation makes the page a test, which is the
 * one study affordance the phrase page never had.
 *
 * It sticks to the top because on an eighty-word phrase you are three screens down by the
 * time you want it, and it carries the reading trail for the same reason.
 */
export default function StudyBar(props: IProps) {
    const {
        action,
        display,
        hasTranslations,
        isSignedIn,
        onToggle,
        openedCount,
        sentenceId,
        totalCount,
    } = props;

    const percentage = totalCount > 0 ? Math.round((openedCount / totalCount) * 100) : 0;

    return <div className="phrase-studybar">
        <div className="phrase-studybar__group">
            <span className="phrase-studybar__legend">Show</span>
            {Modes.map(({ label, mode }) => {
                // A phrase with no translation explains itself rather than greying out in
                // silence, so that case is a control of its own.
                if (mode === 'translation' && ! hasTranslations) {
                    return <MissingEnglishNotice key={mode}
                        isSignedIn={isSignedIn}
                        sentenceId={sentenceId}
                    />;
                }

                // The last script standing stays on: with both off there is no text to read.
                const lastScript = display[mode] && isLastScript(display, mode);

                return <button key={mode}
                    type="button"
                    className="phrase-toggle"
                    aria-pressed={display[mode]}
                    disabled={lastScript}
                    title={lastScript
                        ? `Turn ${mode === 'latin' ? 'the tengwar' : 'the latin'} back on first — the phrase has to be written one way or the other`
                        : undefined}
                    onClick={() => onToggle(mode)}>
                    {label}
                </button>;
            })}
        </div>
        {action}
        <div className="phrase-trail">
            <span className="ed-ui">{openedCount} of {totalCount} words opened</span>
            <span className="phrase-trail__bar" aria-hidden="true">
                <span className="phrase-trail__fill" style={{ width: `${percentage}%` }} />
            </span>
        </div>
    </div>;
}
