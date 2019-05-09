import React, { useCallback } from 'react';

import { fireEvent } from '../Component';
import { IProps } from './TagLabel._types';

function TagLabel(props: IProps) {
    const {
        tag,
        onDelete,
    } = props;

    const _onDeleteClick = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent(`Tag[${tag}]`, onDelete, tag);
    }, [ tag, onDelete ]);

    return <span className="label label-default">
        {tag}
        &#32;
        <a onClick={_onDeleteClick}><span className="glyphicon glyphicon-remove-sign" /></a>
    </span>;
}

export default TagLabel;
