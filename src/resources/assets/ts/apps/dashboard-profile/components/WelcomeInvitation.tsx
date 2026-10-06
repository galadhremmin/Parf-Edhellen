import TextIcon from '@root/components/TextIcon';

import type { IProps } from './WelcomeInvitation._types';

import './WelcomeInvitation.scss';

/**
 * A step of the welcome, offered where its result will appear rather than as an item on a list.
 */
export default function WelcomeInvitation({ step, icon }: IProps) {
    return <div className="WelcomeInvitation">
        <h3 className="WelcomeInvitation--title">{step.title}</h3>
        <p className="WelcomeInvitation--text">{step.text}</p>
        <a href={step.url} className="btn btn-secondary btn-sm">
            {icon && <><TextIcon icon={icon} />{' '}</>}
            {step.action}
        </a>
    </div>;
}
