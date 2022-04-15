import React from 'react';

import Markdown from '@root/components/Markdown';
import { IProps } from './PreviewTabView._types';

function PreviewTabView(props: IProps) {
    const {
        value,
    } = props;

    if (/^\s*$/.test(value)) {
        return <em>There is nothing to preview!</em>;
    } else {
        return <Markdown parse={true} text={value} />;
    }
}

export default PreviewTabView;
