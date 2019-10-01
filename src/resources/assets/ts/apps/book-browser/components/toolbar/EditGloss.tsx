import React from 'react';

import { IProps } from './index._types';

function EditGloss(props: IProps) {
    const {
        gloss,
    } = props;

    return <>
        <a href={`/dashboard/contribution/create/gloss?entity_id=${gloss.id}`}>
            <span className="glyphicon glyphicon-pencil" />
        </a>
    </>;
}

export default EditGloss;
