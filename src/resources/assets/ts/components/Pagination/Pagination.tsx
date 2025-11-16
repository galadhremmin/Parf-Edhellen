import classNames from 'classnames';
import { type IProps, PageModes } from './Pagination._types';
import PaginationLink from './PaginationLink';
import {
    createPageArray,
    getFirstPageNumber,
    isPageArrayTruncated,
} from './utils';

const Pagination = (props: IProps) => {
    const {
        currentPage = 1,
        noOfPages = 1,
        pageQueryParameterName = 'offset',
        onClick,
        pages = null,
    } = props;

    if (noOfPages < 2) {
        return null;
    }

    let pageArray: (number | string)[];
    switch (pages) {
        case PageModes.AutoGenerate:
            pageArray = createPageArray(noOfPages, currentPage);
            break;
        case PageModes.None:
            pageArray = [];
            break;
        default:
            pageArray = pages || [];
    }

    const truncated = isPageArrayTruncated(pageArray, noOfPages);

    return <nav>
        <ul className="pagination justify-content-center">
            {currentPage > getFirstPageNumber() && <li className="page-item">
                <PaginationLink pageNumber={truncated ? getFirstPageNumber() : currentPage - 1}
                    parameterName={pageQueryParameterName}
                    onClick={onClick}>
                    <span aria-hidden="true">← </span>
                    {truncated ? 'First' : 'Previous'}
                </PaginationLink>
            </li>}
            {pageArray.map((pageNumber) => <li key={pageNumber}
                className={classNames('page-item', { active: currentPage === pageNumber})}>
                <PaginationLink pageNumber={pageNumber}
                    onClick={onClick}
                    parameterName={pageQueryParameterName}>
                    {pageNumber}
                </PaginationLink>
            </li>)}
            {currentPage < noOfPages && <li className="page-item">
                <PaginationLink pageNumber={truncated ? noOfPages : currentPage + 1}
                    parameterName={pageQueryParameterName}
                    onClick={onClick}>
                    {truncated ? 'Last' : 'Next'}
                    <span aria-hidden="true"> →</span>
                </PaginationLink>
            </li>}
        </ul>
    </nav>;
};

export default Pagination;
