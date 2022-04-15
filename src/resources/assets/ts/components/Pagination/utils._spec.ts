import { expect } from 'chai';

import { createPageArray, getFirstPageNumber, isPageArrayTruncated } from './utils';

describe('components/Pagination/utils', () => {
    it('handles array within bounds', () => {
        const numberOfPages = 8;
        const currentPage = 8;

        const expected = [1,2,3,4,5,6,7,8];
        const actual = createPageArray(numberOfPages, currentPage, 8);

        expect(actual).to.deep.equal(expected);
    });
    it('handles short array', () => {
        const numberOfPages = 3;
        const currentPage = 1;

        const expected = [1,2,3];
        const actual = createPageArray(numberOfPages, currentPage, 5);

        expect(actual).to.deep.equal(expected);
    });
    it('handles middle of long array', () => {
        const numberOfPages = 100;
        const currentPage = 50;

        const expected = [48,49,50,51,52];
        const actual = createPageArray(numberOfPages, currentPage, 6);

        expect(actual).to.deep.equal(expected);
    });
    it('handles end of long array', () => {
        const numberOfPages = 100;
        const currentPage = 99;

        const expected = [95,96,97,98,99,100];
        const actual = createPageArray(numberOfPages, currentPage, 6);

        expect(actual).to.deep.equal(expected);
    });
    it('handles start of long array', () => {
        const numberOfPages = 100;
        const currentPage = 2;

        const expected = [1,2,3,4,5,6];
        const actual = createPageArray(numberOfPages, currentPage, 6);

        expect(actual).to.deep.equal(expected);
    });
    it('identifies pre-truncated array', () => {
        const pages = [2,3,4,5,6];
        const actual = isPageArrayTruncated(pages, 6);

        expect(actual).to.be.true;
    });
    it('identifies end-truncated array', () => {
        const pages = [1,2,3,4,5];
        const actual = isPageArrayTruncated(pages, 6);

        expect(actual).to.be.true;
    });
    it('identifies non-truncated array', () => {
        const pages = [1,2,3,4,5,6];
        const actual = isPageArrayTruncated(pages, 6);

        expect(actual).to.be.false;
    });
    it('first page number is 1', () => {
        const actual = getFirstPageNumber();
        expect(actual).to.equal(1);
    });
});
