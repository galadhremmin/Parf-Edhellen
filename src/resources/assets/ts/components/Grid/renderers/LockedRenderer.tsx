import type {
    ICellRendererParams,
} from '@ag-grid-community/core';
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
