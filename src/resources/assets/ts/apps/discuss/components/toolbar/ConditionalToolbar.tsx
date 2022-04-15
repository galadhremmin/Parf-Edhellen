import React, { Suspense } from 'react';

import Spinner from '@root/components/Spinner';
import { IProps } from './ConditionalToolbar._types';

export default function ConditionalToolbar(props: IProps) {
    return <Suspense fallback={<Spinner />}>
        <ToolbarAsync {...props} />
    </Suspense>;
}

const ToolbarAsync = React.lazy(() => import('.'));
