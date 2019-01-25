import { ComponentEventHandler } from './Component._types';

export interface IProps {
    currentPage: number;
    noOfPages: number;
    onClick?: ComponentEventHandler<number>;
    pageQueryParameterName?: string;
    pages?: Array<string | number>;
}
