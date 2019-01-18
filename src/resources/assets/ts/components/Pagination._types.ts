export interface IProps {
    currentPage: number;
    noOfPages: number;
    pageQueryParameterName?: string;
    pages: Array<string | number>;
}
