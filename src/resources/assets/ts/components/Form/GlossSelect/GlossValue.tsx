import React from 'react';

import { ISuggestionEntity } from '@root/connectors/backend/IBookApi';
import GlossSuggestion from './GlossSuggestion';

function GlossValue(gloss: ISuggestionEntity) {
    if (gloss === null) {
        return <span>No gloss</span>;
    }

    return GlossSuggestion(gloss);
}

export default GlossValue;
