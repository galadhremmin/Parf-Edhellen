import React from 'react';

import Markdown from '@root/components/Markdown';
import { IProps } from './PreviewTabView._types';

import './PreviewTabView.scss';

function PreviewTabView(props: IProps) {
    const {
        markdownApi,
        value,
    } = props;

    if (/^\s*$/.test(value)) {
        return <em>There is nothing to preview!</em>;
    } else {
        return <div className="PreviewTabView"><Markdown parse={true} text={value} markdownApi={markdownApi} /></div>;
    }
}

export default PreviewTabView;
