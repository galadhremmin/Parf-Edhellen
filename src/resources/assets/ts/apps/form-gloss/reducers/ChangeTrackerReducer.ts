import { Actions } from "../actions";
import { IChangeTrackerReducerState } from "./ChangeTrackerReducer._types";
import { ILexicalEntryAction } from "./LexicalEntryReducer._types";
import { IInflectionAction } from "./InflectionsReducer._types";

const InitialState: IChangeTrackerReducerState = {
    lexicalEntryChanged: false,
    inflectionsChanged: false,
};

const ChangeTrackerReducer = (state = InitialState, action: ILexicalEntryAction | IInflectionAction): IChangeTrackerReducerState => {
    switch (action.type) {
        case Actions.SetLexicalEntryField:
            return {
                ...state,
                lexicalEntryChanged: true,
            };

        case Actions.CreateBlankInflectionGroup:
        case Actions.SetInflectionGroup:
        case Actions.UnsetInflectionGroup:
            return {
                ...state,
                inflectionsChanged: true,
            };

        default:
            return state;
    }
};

export default ChangeTrackerReducer;
