import React from 'react';

import Quote from '@root/components/Quote';
import { ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';

import './GlossSuggestion.scss';

const GlossSuggestion = (gloss: ISuggestionEntity) => {
    return <span title={gloss.comments || ''}>
        <span className="GlossSuggestion--id">{gloss.id}</span>
        <span className="GlossSuggestion--gloss">{gloss.word}</span>{' '}
        <span className="GlossSuggestion--type">{gloss.type}</span>{' '}
        <span className="GlossSuggestion--trans"><Quote>{gloss.translation}</Quote></span>{' '}
        {gloss.source && `[${gloss.source}]`}{' '}
        {gloss.glossGroupName && `in ${gloss.glossGroupName}`}
    </span>;
};

export default GlossSuggestion;
