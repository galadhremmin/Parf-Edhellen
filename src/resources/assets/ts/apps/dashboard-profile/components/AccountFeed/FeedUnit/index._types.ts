import { IFeedRecord } from "@root/connectors/backend/IAccountApi";

export interface IProps<TEntity extends IFeedRecord = IFeedRecord> {
    unit: TEntity;
    first?: boolean;
    visible?: boolean;
}
