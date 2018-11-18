import React from 'react';

import { IProps } from './Paragraph._types';

import '@root/components/Tengwar';
import './Paragraph.scss';

const Paragraph = (props: IProps) => {
    return <div className={`p-group__p ${props.transformerName}`} data-paragraph={props.paragraphNumber}>
        {props.children}
    </div>;
};

export default Paragraph;
