import classNames from 'classnames';
import type { IProps } from './TextIcon._types';

import './TextIcon.scss';

function TextIcon({ color, className, icon }: IProps) {
    const style = color ? { backgroundColor: color } : null;
    return <span
        className={classNames(`TextIcon TextIcon--${icon}`, className || '')}
        style={style}
    />;
}

export default TextIcon;
