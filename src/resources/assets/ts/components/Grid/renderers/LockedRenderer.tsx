import React from 'react';
import {
    ICellRendererParams,
} from 'ag-grid-community';
import TextIcon from '@root/components/TextIcon';
import { isEmptyString } from '@root/utilities/func/string-manipulation';

const LockedRenderer = (params: ICellRendererParams) => {
    if (isEmptyString(params.value)) {
        return null;
    }

    return <span className="text-muted">
        <TextIcon icon="lock" />
        {params.value}
    </span>;
};

export default LockedRenderer;
