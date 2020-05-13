import React, { useState, useCallback } from 'react';
import GlossGroupSelect from '@root/components/Form/GlossGroupSelect';
import SpeechSelect from '@root/components/Form/SpeechSelect';

import { IProps } from './AdditionalSearchParameters._types';

import './AdditionalSearchParameters.scss';

function AdditionalSearchParameters(props: IProps) {
    const {
        glossGroupId,
        onGlossGroupIdChange,
        onSpeechIdChange,
        speechId,
    } = props;

    return <div className="AdditionalSearchParameters">
        <SpeechSelect
            allowEmpty={true}
            emptyText="Any parts of speech"
            name="speech"
            onChange={onSpeechIdChange}
            value={speechId}
        />
        <GlossGroupSelect
            allowEmpty={true}
            emptyText="Any group"
            name="gloss-group"
            onChange={onGlossGroupIdChange}
            value={glossGroupId}
        />
    </div>
}

AdditionalSearchParameters.defaultProps = {
    glossGroupId: 0,
    speechId: 0,
} as Partial<IProps>;

export default AdditionalSearchParameters;
