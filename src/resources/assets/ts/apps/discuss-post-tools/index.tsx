import React from 'react';
import { IProps } from './containers/Toolbar._types';
import Toolbar from './containers/Toolbar';

const Inject = (props: IProps) => <Toolbar {...props} />;
export default Inject;
