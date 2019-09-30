import React from 'react';
import { IProps } from './Quote._types';

const Quote = (props: IProps) => <>
    &ldquo;{props.children}&rdquo;
</>;

export default Quote;
