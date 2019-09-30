import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';

import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import { ISuggestionEntity } from '@root/connectors/backend/BookApiConnector._types';
import { mapper } from '@root/utilities/func/mapper';
import SharedReference from '@root/utilities/SharedReference';

import { fireEvent } from '../../Component';
import { IComponentEvent } from '../../Component._types';
import EntitySelect from '../EntitySelect';
import { IProps } from './GlossSelect._types';
import GlossSuggestion from './GlossSuggestion';
import GlossValue from './GlossValue';

const glossFormatter = (gloss: ISuggestionEntity) => gloss ? `${gloss.word} (${gloss.id})` : '';

function GlossSelect(props: IProps) {
    const {
        apiConnector,
        name,
        onChange,
        value,
    } = props;

    const [ suggestions, setSuggestions ] = useState([]);
    const [ complexValue, setComplexValue ] = useState<ISuggestionEntity>(null);

    useEffect(() => {
        let cancelled = false;
        if (value && (complexValue === null || value !== complexValue.id)) {
            // Resolve numeric values to complex value. The complex value is local state
            // used by the component to visualize the gloss in a human-readable format.
            apiConnector.gloss(value)
                .then((r) => {
                    if (cancelled) {
                        return;
                    }

                    const gloss = r.sections[0].glosses[0];
                    const suggestion = mapper<typeof gloss, ISuggestionEntity>({
                        accountName: 'accountName',
                        comments: 'comments',
                        glossGroupName: 'glossGroupName',
                        id: 'id',
                        normalizedWord: 'normalizedWord',
                        source: 'source',
                        translation: 'allTranslations',
                        type: 'type',
                        word: 'word',
                    }, gloss);

                    setComplexValue(suggestion);
                }).catch(() => {
                    setComplexValue(null);
                });
        }
        return () => { cancelled = true; };
    }, [ complexValue, setComplexValue, value ]);

    const _onClearSuggestions = useCallback(() => {
        setSuggestions([]);
    }, [ setSuggestions ]);

    const _onSuggest = useCallback(async (ev: IComponentEvent<string>) => {
        const word = ev.value.replace(/\s\(\d+\)$/, '');

        const newSuggestions = await apiConnector.suggest({
            inexact: true,
            words: [ word ],
        });

        setSuggestions(newSuggestions[word] || []);
    }, [ apiConnector ]);

    const _onChange = useCallback((ev: IComponentEvent<ISuggestionEntity>) => {
        setComplexValue(ev.value);
        fireEvent(name, onChange, ev.value ? ev.value.id : null);
    }, [ setComplexValue, name, onChange ]);

    return <EntitySelect<ISuggestionEntity>
        formatter={glossFormatter}
        name={name}
        onChange={_onChange}
        onClearSuggestions={_onClearSuggestions}
        onSuggest={_onSuggest}
        renderSuggestion={GlossSuggestion}
        renderValue={GlossValue}
        suggestions={suggestions}
        value={complexValue}
        valueClassNames="GlossSelect--value"
    />;
}

GlossSelect.defaultProps = {
    apiConnector: SharedReference.getInstance(BookApiConnector),
    value: 0,
} as Partial<IProps>;

export default GlossSelect;
