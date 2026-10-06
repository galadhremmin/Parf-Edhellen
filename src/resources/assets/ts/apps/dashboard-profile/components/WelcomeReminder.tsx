import type { IProps } from './WelcomeReminder._types';

import './WelcomeProgress.scss';

/**
 * Where the welcome was before it was hidden: a quiet way to bring it back while steps are left.
 */
export default function WelcomeReminder({ pending, onShow }: IProps) {
    return <div className="WelcomeProgress">
        <p className="WelcomeProgress--caption">
            <span className="WelcomeProgress--mark" aria-hidden="true">&#10022;</span>
            {' '}
            <span className="ed-ui">
                {pending === 1 ? '1 thing' : `${pending} things`} left to make this page yours
            </span>
            <button type="button" className="btn btn-link btn-sm p-0" onClick={onShow}>
                Show me
            </button>
        </p>
    </div>;
}
