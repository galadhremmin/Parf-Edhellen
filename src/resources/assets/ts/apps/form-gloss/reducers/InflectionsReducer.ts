import { pickProps } from '@root/utilities/func/props';
import { Actions } from '../actions';
import { GroupedInflectionsState, IInflectionAction } from './InflectionsReducer._types';

const InflectionsReducer = (state: GroupedInflectionsState = [], action: IInflectionAction): GroupedInflectionsState => {
    switch (action.type) {
        case Actions.ReceiveInflections: {
            const nextInflections = Object.keys(action.preloadedInflections).map((inflectionGroup) => ({
                ...pickProps(action.preloadedInflections[inflectionGroup][0], [
                    'glossId',
                    'inflectionGroupUuid',
                    'isNeologism',
                    'isRejected',
                    'languageId',
                    'sentenceFragmentId',
                    'source',
                    'speechId',
                    'word',
                    'sentence'
                ]),
                inflections: action.preloadedInflections[inflectionGroup],
            }));

            return nextInflections;
        }
        case Actions.SetInflectionGroup:
            return state.map((i) => {
                if (i.inflectionGroupUuid === action.inflectionGroupUuid) {
                    return action.inflectionGroup;
                }
                return i;
            });
        case Actions.UnsetInflectionGroup:
            return state.filter((i) => i.inflectionGroupUuid !== action.inflectionGroupUuid);
        case Actions.CreateBlankInflectionGroup:
            return [
                ...state,
                {
                    inflections: [],
                    inflectionGroupUuid: crypto.randomUUID(),
                    word: '',
                },
            ];
        default:
            return state;
    }
};

export default InflectionsReducer;
