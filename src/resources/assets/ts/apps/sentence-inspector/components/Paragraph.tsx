import React from 'react';

import { IProps } from './Paragraph._types';

import '@root/components/Tengwar';
import './Paragraph.scss';

const Paragraph = (props: IProps) => {
    return <div className={`paragraph ${props.transformerName}`}>
        {props.children}
    </div>
};

export default Paragraph;
