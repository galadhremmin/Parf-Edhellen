import { ComponentEventHandler } from '../Component._types';

export const enum PageModes {
    AutoGenerate = 'auto',
    None = 'none',
}

export interface IProps {
    currentPage: number;
    noOfPages: number;
    onClick?: ComponentEventHandler<number>;
    pageQueryParameterName?: string;
    pages?: (string | number)[] | PageModes;
}
