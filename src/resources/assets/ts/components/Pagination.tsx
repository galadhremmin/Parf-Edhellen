import React from 'react';

import { IProps } from './Pagination._types';
import PaginationLink from './PaginationLink';

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

    return <nav className="text-center">
        <ul className="pagination">
            {currentPage > 1 && <li>
                <PaginationLink pageNumber={currentPage - 1}
                    onClick={onClick}>
                    <span aria-hidden="true">← </span>
                    Older
                </PaginationLink>
            </li>}
            {pages.map((pageNumber) => <li key={pageNumber}
                className={currentPage === pageNumber ? 'active' : ''}>
                <PaginationLink pageNumber={pageNumber}
                    onClick={onClick}>
                    {pageNumber}
                </PaginationLink>
            </li>)}
            {currentPage < noOfPages && <li>
                <PaginationLink pageNumber={currentPage + 1}
                    onClick={onClick}>
                    Newer
                    <span aria-hidden="true"> →</span>
                </PaginationLink>
            </li>}
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
