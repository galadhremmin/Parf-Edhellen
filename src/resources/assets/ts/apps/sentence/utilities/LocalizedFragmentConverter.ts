import {
    ISentenceFragmentEntity,
    SentenceFragmentLocalizationMap,
    SentenceLocalizedTransformationMap,
} from '@root/connectors/backend/BookApiConnector._types';
import { mapArray } from '@root/utilities/func/mapper';
import { ILocalizedFragmentsReducerState } from '../reducers/FragmentsReducer._types';

const convert = (map: SentenceLocalizedTransformationMap[], fragments: ISentenceFragmentEntity[]) => {
    if (map === undefined || map === null) {
        return [];
    }

    const lines = [];

    for (const lineMap of map) {
        const state = mapArray<SentenceFragmentLocalizationMap, ILocalizedFragmentsReducerState>({
            fragment: (v) => typeof v === 'string'
                ? v : v[1] !== undefined ? v[1] : fragments[v[0]].fragment,
            id: (v) => typeof v === 'string'
                ? -1 : fragments[v[0]].id,
        }, lineMap);

        lines.push(state);
    }

    return lines;
};

export default convert;
