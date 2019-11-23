import { ComponentEventHandler } from '@root/components/Component._types';
import { IErrorEntity } from '@root/connectors/backend/ILogApi';

export interface IProps {
    currentPage: number;
    logs: IErrorEntity[];
    onClick: ComponentEventHandler<number>;
    noOfPages: number;
}
