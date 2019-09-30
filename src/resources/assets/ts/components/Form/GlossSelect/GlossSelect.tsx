import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';

import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import { ISuggestionEntity } from '@root/connectors/backend/BookApiConnector._types';
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
    const [ complexValue, setComplexValue ] = useState(null);

    useEffect(() => {
        if (value !== 0 && (complexValue === null || value !== complexValue.id)) {
            setComplexValue(_createComplex(value));
        }
    }, [ complexValue, setComplexValue, value ]);

    const _onClearSuggestions = useCallback(() => {
        setSuggestions([]);
    }, [ setSuggestions ]);

    const _onSuggest = useCallback(async (ev: IComponentEvent<string>) => {
        const newSuggestions = await apiConnector.suggest({
            inexact: true,
            words: [ ev.value ],
        });

        setSuggestions(newSuggestions[ev.value] || []);
    }, [ apiConnector ]);

    const _onChange = useCallback((ev: IComponentEvent<ISuggestionEntity>) => {
        setComplexValue(ev.value);
        fireEvent(name, onChange, ev.value.id);
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

const _createComplex = (value: number) => ({
    accountName: '',
    id: value,
}) as ISuggestionEntity;

GlossSelect.defaultProps = {
    apiConnector: SharedReference.getInstance(BookApiConnector),
    value: 0,
} as Partial<IProps>;

export default GlossSelect;
