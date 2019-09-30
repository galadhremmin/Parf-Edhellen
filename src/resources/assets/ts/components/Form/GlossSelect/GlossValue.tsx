import React, { useEffect, useState } from 'react';

import { ISuggestionEntity } from '@root/connectors/backend/BookApiConnector._types';
import GlossSuggestion from './GlossSuggestion';

function GlossValue(gloss: ISuggestionEntity) {
    if (gloss === null) {
        return <span>No gloss</span>;
    }

    return GlossSuggestion(gloss);
}

export default GlossValue;
