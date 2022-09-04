import { Actions } from "../actions";
import { IChangeTrackerReducerState } from "./ChangeTrackerReducer._types";
import { IGlossAction } from "./GlossReducer._types";
import { IInflectionAction } from "./InflectionsReducer._types";

const InitialState: IChangeTrackerReducerState = {
    glossChanged: false,
    inflectionsChanged: false,
};

const ChangeTrackerReducer = (state = InitialState, action: IGlossAction | IInflectionAction): IChangeTrackerReducerState => {
    switch (action.type) {
        case Actions.SetGlossField:
            return {
                ...state,
                glossChanged: true,
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
