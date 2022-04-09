import classNames from 'classnames';
import React from 'react';

import { IProps, PageModes } from './Pagination._types';
import PaginationLink from './PaginationLink';
import { createPageArray } from './utils';

const Pagination = (props: IProps) => {
    const {
        currentPage,
        noOfPages,
        onClick,
        pages,
    } = props;

    if (noOfPages < 2) {
        return null;
    }

    let pageArray: (number | string)[];
    switch (pages) {
        case PageModes.AutoGenerate:
            pageArray = createPageArray(noOfPages);
            break;
        case PageModes.None:
            pageArray = [];
            break;
        default:
            pageArray = pages || [];
    }

    return <nav className="text-center">
        <ul className="pagination">
            <li className={classNames('page-item', { disabled: currentPage <= 1 })}>
                <PaginationLink pageNumber={currentPage - 1}
                    onClick={onClick}>
                    <span aria-hidden="true">← </span>
                    Previous
                </PaginationLink>
            </li>
            {pageArray.map((pageNumber) => <li key={pageNumber}
                className={classNames('page-item', { active: currentPage === pageNumber})}>
                <PaginationLink pageNumber={pageNumber}
                    onClick={onClick}>
                    {pageNumber}
                </PaginationLink>
            </li>)}
            <li className={classNames('page-item', { disabled: currentPage >= noOfPages })}>
                <PaginationLink pageNumber={currentPage + 1}
                    onClick={onClick}>
                    Next
                    <span aria-hidden="true"> →</span>
                </PaginationLink>
            </li>
        </ul>
    </nav>;
};

Pagination.defaultProps = {
    currentPage: 1,
    noOfPages: 1,
    pageQueryParameterName: 'offset',
    pages: null,
} as Partial<IProps>;

export default Pagination;
