import React from 'react';

import { IProps } from './StaticAlert._types';

const StaticAlert = (props: IProps) => <div className={`alert alert-${props.type}`}>
    {props.children}
</div>;

export default StaticAlert;
