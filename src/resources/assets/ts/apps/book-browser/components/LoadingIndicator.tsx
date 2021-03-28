import React from 'react';

import Spinner from '@root/components/Spinner';
import { IProps } from './LoadingIndicator._types';

import './LoadingIndicator.scss';

function LoadingIndicator({ text }: IProps) {
    return <div className="LoadingIndicator">
        <Spinner />
        <p>{text}</p>
    </div>;
}

export default LoadingIndicator;
