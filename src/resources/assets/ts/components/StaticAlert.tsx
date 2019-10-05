import React from 'react';

import { IProps } from './StaticAlert._types';

const StaticAlert: React.FC<IProps> = (props: IProps) => <div className={`alert alert-${props.type}`}>
    {props.children}
</div>;

StaticAlert.defaultProps = {
    type: 'info',
};

export default StaticAlert;
