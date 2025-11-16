import { lazy, Suspense } from 'react';

import Spinner from '@root/components/Spinner';
import type { IProps } from './ConditionalToolbar._types';

export default function ConditionalToolbar(props: IProps) {
    return <Suspense fallback={<Spinner />}>
        <ToolbarAsync {...props} />
    </Suspense>;
}

const ToolbarAsync = lazy(() => import('./index'));
