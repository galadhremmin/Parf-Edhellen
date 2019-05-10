import React, { useCallback } from 'react';

import { fireEvent } from '../Component';
import { IProps } from './TagLabel._types';

function TagLabel(props: IProps) {
    const {
        tag,
        onDelete,
    } = props;

    const _onTagClick = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        if (! ev.target.checked) {
            fireEvent(`Tag[${tag}]`, onDelete, tag);
        }
    }, [ tag, onDelete ]);

    return <label className="label label-default">
        <input checked={true}
               name={`tag-checkbox--${tag}`}
               onChange={_onTagClick}
               type="checkbox"
        />
        {tag}
        &#32;
        <span className="glyphicon glyphicon-remove-sign" />
    </label>;
}

export default TagLabel;
