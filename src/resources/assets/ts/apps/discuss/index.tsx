import React from 'react';

import Discuss from './containers/Discuss';
import { IProps } from './index._types';

const Inject = (props: IProps) => <Discuss {...props} />;

export default Inject;
