import React from 'react';
import { IProps } from './Fragment._types';

const Fragments = (props: IProps) => props.fragment.id > -1 // 
    ? <a href="">
        {props.fragment.fragment}
    </a> :
    <span>{props.fragment.fragment}</span>;

export default Fragments;
