import React from 'react';

import TextIcon from '@root/components/TextIcon';
import { IProps } from './index._types';

function EditGloss(props: IProps) {
    const {
        gloss,
    } = props;

    return <>
        <a href={`/dashboard/contribution/create/gloss?entity_id=${gloss.id}`}>
            <TextIcon icon="pencil" />
        </a>
    </>;
}

export default EditGloss;
