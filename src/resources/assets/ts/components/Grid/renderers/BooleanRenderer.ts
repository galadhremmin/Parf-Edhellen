import {
    ICellRendererParams,
} from '@ag-grid-community/core';

const BooleanRenderer = (params: ICellRendererParams) => {
    const value = typeof params.value === 'boolean' ? params.value : !! params.value;
    return value ? 'Yes' : '-';
};

export default BooleanRenderer;
