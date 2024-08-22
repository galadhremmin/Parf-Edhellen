import { IFeedRecord } from "@root/connectors/backend/IAccountApi";

export interface IProps<TEntity extends IFeedRecord = any> {
    unit: TEntity;
    visible?: boolean;
}
