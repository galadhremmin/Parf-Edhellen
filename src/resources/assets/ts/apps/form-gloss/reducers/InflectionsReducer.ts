import { Actions } from '../actions';
import { GroupedInflectionsState, IInflectionAction } from './InflectionsReducer._types';

const InflectionsReducer = (state: GroupedInflectionsState = [], action: IInflectionAction): GroupedInflectionsState => {
    switch (action.type) {
        case Actions.ReceiveInflections: {
            const nextInflections = action.preloadedInflections.reduce<GroupedInflectionsState>((carry, inflection) => {
                var pos = carry.findIndex((i) => i.inflectionGroupUuid === action.inflectionGroupUuid);
                if (pos === -1) {
                    carry.push({
                        ...inflection,
                        inflections: [inflection],
                    });
                } else {
                    carry[pos].inflections.push(inflection);
                }

                return carry;
            }, []);

            return nextInflections;
        }

        case Actions.SetInflectionGroup:
            return {
                ...state,
                [action.inflectionGroupUuid]: action.inflectionGroup,
            };
        case Actions.UnsetInflectionGroup:
            return state.filter((i) => i.inflectionGroupUuid !== action.inflectionGroupUuid);
        default:
            return state;
    }
};

export default InflectionsReducer;
