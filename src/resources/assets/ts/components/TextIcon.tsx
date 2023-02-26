import classNames from 'classnames';
import { IProps } from './TextIcon._types';

import './TextIcon.scss';

function TextIcon({ className, icon }: IProps) {
    return <span className={classNames(`TextIcon TextIcon--${icon}`, className || '')} />;
}

export default TextIcon;
