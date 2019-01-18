import React from 'react';
import { IProps } from './Pagination._types';

const createLink = (pageNumber: string | number, parameterName: string = 'offset') =>
    `?${parameterName}=${pageNumber}`;

const Pagination = (props: IProps) => {
    if (props.noOfPages < 2) {
        return null;
    }

    const parameterName = props.pageQueryParameterName;

    return <nav className="text-center">
        <ul className="pagination">
            {props.currentPage > 1 && <li>
                <a href={createLink(props.currentPage - 1, parameterName)}>
                    <span aria-hidden="true">← </span>
                    Older
                </a>
            </li>}
            {props.pages.map((pageNumber) => <li key={pageNumber}
                className={props.currentPage === pageNumber ? 'active' : ''}>
                <a href={createLink(pageNumber, parameterName)}>
                    {pageNumber}
                </a>
            </li>)}
            {props.currentPage < props.noOfPages && <li>
                <a href={createLink(props.currentPage + 1, parameterName)}>
                    Newer
                    <span aria-hidden="true"> →</span>
                </a>
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
