import { IInflectionResourceApi } from "@root/connectors/backend/IInflectionResourceApi";

export interface IProps {
    lexicalEntryId: number;
    open: boolean;
    onDismiss: () => void;
    inflectionApi?: IInflectionResourceApi;
}
