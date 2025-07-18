import React, { Suspense } from 'react';

import Spinner from '@root/components/Spinner';
import { IProps } from './index._types';
import registerApp from '../app';

const Inject = (props: IProps) => {
    const {
        container,
    } = props;

    const ContainerAsync = React.lazy(() => import(`./containers/${container}`));
    return <Suspense fallback={<Spinner />}>
        <ContainerAsync {...props} />
    </Suspense>;
};

export default registerApp(Inject);
