import type { DisplayMode } from '../reducers/DisplayReducer._types';
import type { IProps } from './StudyBar._types';

import './StudyBar.scss';

const Modes: { label: string; mode: DisplayMode }[] = [
    { label: 'Tengwar', mode: 'tengwar' },
    { label: 'Latin', mode: 'latin' },
    { label: 'Translation', mode: 'translation' },
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
        onToggle,
        openedCount,
        totalCount,
    } = props;

    const percentage = totalCount > 0 ? Math.round((openedCount / totalCount) * 100) : 0;

    return <div className="phrase-studybar">
        <div className="phrase-studybar__group">
            <span className="phrase-studybar__legend">Show</span>
            {Modes.map(({ label, mode }) => {
                const isTranslation = mode === 'translation';
                const disabled = isTranslation && ! hasTranslations;
                return <button key={mode}
                    type="button"
                    className="phrase-toggle"
                    aria-pressed={! disabled && display[mode]}
                    disabled={disabled}
                    title={disabled ? 'This phrase has no translation recorded' : undefined}
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
