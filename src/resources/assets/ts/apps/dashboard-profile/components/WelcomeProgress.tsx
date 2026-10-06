import type { IProps } from './WelcomeProgress._types';

import './WelcomeProgress.scss';

/**
 * How far the new member's page has come, with a way to put the hints away.
 */
export default function WelcomeProgress({ done, total, onHide }: IProps) {
    return <div className="WelcomeProgress">
        <div className="ed-progress" role="progressbar" aria-valuemin={0} aria-valuemax={total} aria-valuenow={done}
            aria-label={`${done} of ${total} done`}>
            <span style={{ width: `${Math.round(done / total * 100)}%` }} />
        </div>
        <p className="WelcomeProgress--caption">
            <span className="WelcomeProgress--mark" aria-hidden="true">&#10022;</span>
            {' '}
            <span className="ed-ui">
                {done} of {total}
                {' · '}
                {done === 0 ? 'Make this page yours' : 'Your page is taking shape'}
            </span>
            <button type="button" className="btn btn-link btn-sm p-0 WelcomeProgress--hide" onClick={onHide}>
                Hide these hints
            </button>
        </p>
    </div>;
}
