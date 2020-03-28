import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';

import {
    ISuggestionEntity,
} from '@root/connectors/backend/IGlossResourceApi';
import { DI, resolve } from '@root/di';
import { mapper } from '@root/utilities/func/mapper';

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

                    const suggestion = mapper<typeof r, ISuggestionEntity>({
                        accountName: (v) => v.account.nickname,
                        comments: 'comments',
                        glossGroupName: (v) => v.glossGroup?.name,
                        id: 'id',
                        normalizedWord: (v) => v.word.normalizedWord,
                        source: 'source',
                        translation: (v) => v.translations.map((t) => t.translation).join(', '),
                        type: (v) => v.speech?.name,
                        word: (v) => v.word.word,
                    }, r);

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
        const word = ev.value.trim();

        let newSuggestions: ISuggestionEntity[] = [];
        if (word.length > 0) {
            const suggestionMap = await apiConnector.suggest({
                inexact: true,
                parameterized: true,
                words: [ word ],
            });

            if (suggestionMap.size > 0) {
                newSuggestions = suggestionMap.values().next().value;
            }
        }

        setSuggestions(newSuggestions);
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
    apiConnector: resolve(DI.GlossApi),
    value: 0,
} as Partial<IProps>;

export default GlossSelect;
