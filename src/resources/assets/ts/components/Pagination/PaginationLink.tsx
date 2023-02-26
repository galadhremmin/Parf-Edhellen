import React, { useEffect, useState } from 'react';

import { updateQueryString } from '@root/utilities/func/query-string';
import { fireEvent } from '../Component';
import { IProps } from './PaginationLink._types';

function PaginationLink(props: IProps) {
    const {
        children,
        onClick,
        pageNumber,
        parameterName,
    } = props;

    const [ queryString, setQueryString ] = useState<string>('?');

    const _onClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        if (onClick !== null) {
            ev.preventDefault();
            fireEvent(this, onClick, pageNumber);
        }
    }

    useEffect(() => {
        const nextQueryString = updateQueryString({
            [parameterName]: pageNumber,
        });

        setQueryString(nextQueryString);
    }, [ pageNumber, parameterName, window.location.search ]);

    return <a href={queryString} onClick={_onClick} className="page-link">
        {children || pageNumber}
    </a>;
}

PaginationLink.defaultProps = {
    onClick: null,
    parameterName: 'offset',
} as Partial<IProps>;

export default PaginationLink;
