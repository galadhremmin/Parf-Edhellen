import { ComponentEventHandler } from "@root/components/Component._types";
import { ISentenceFieldChangeSpec } from "../containers/SentenceForm._types";
import { ISentenceReducerState } from "../reducers/SentenceReducer._types";

export interface ILanguageMetadataFormEvents {
    onLanguageChange?: ComponentEventHandler<ISentenceFieldChangeSpec>;
}

export interface IProps extends ILanguageMetadataFormEvents {
    sentence: ISentenceReducerState;
}