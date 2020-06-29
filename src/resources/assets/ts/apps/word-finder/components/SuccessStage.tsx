import React, { useEffect } from 'react';

import { fireEvent } from '@root/components/Component';

import { IStageProps } from '../index._types';

function SuccessStage(props: IStageProps) {
    const {
        // onUpdateStage,
    } = props;

    useEffect(() => {
        setTimeout(() => {
            // fireEvent('SuccessStage', onUpdateStage);
        }, 2000);
    }, []);

    return <span>
        Correct!
    </span>
}

export default SuccessStage;
