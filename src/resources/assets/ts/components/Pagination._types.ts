export interface IProps {
    currentPage: number;
    noOfPages: number;
    pages: Array<string | number>;
    pageQueryParameterName?: string;
}