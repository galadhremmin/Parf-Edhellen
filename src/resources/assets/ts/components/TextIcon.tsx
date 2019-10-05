import classNames from 'classnames';
import React from 'react';
import { IProps } from './TextIcon._types';

const TextIcon: React.FC<IProps> = (props: IProps) => <span
    className={classNames(`glyphicon glyphicon-${props.icon}`, props.className || '')}
/>;

export default TextIcon;
