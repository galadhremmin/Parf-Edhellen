const DEFAULT_MAXIMUM_PAGES = 6;
const FIRST_PAGE_NUMBER = 1;

export const createPageArray = (noOfPages = 0, currentPage: number, maximumPages = DEFAULT_MAXIMUM_PAGES) => {
    const pages: number[] = [];

    const modifier = Math.floor(maximumPages / 2) - 1;
    const firstPage = getFirstPageNumber();
    let start = currentPage - modifier;
    let end = currentPage + modifier;

    if (start < firstPage) {
        start = firstPage;
        end   = Math.min(start + maximumPages - 1, noOfPages);
    } else if (end > noOfPages) {
        start = Math.max(noOfPages - maximumPages + 1, firstPage);
        end   = Math.min(start + maximumPages, noOfPages);
    }

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
};

export const isPageArrayTruncated = (array: (number | string)[], numberOfPages: number) => {
    if (! array || ! Array.isArray(array)) {
        return false;
    }

    return array[array.length - 1] < numberOfPages || array[0] > getFirstPageNumber();
}

export const getFirstPageNumber = () => FIRST_PAGE_NUMBER;
