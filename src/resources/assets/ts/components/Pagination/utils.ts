export const createPageArray = (noOfPages: number = 0, pages: Array<number | string> = null) => {
    if (pages !== null) {
        return pages;
    }

    pages = [];
    for (let page = 1; page <= noOfPages; page += 1) {
        pages.push(page);
    }

    return pages;
};
