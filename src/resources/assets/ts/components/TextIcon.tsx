import classNames from 'classnames';
import { IProps } from './TextIcon._types';

import './TextIcon.scss';

const TextIcon: React.FC<IProps> = (props: IProps) => <span
    className={classNames(`TextIcon TextIcon--${props.icon}`, props.className || '')}
/>;

export default TextIcon;
