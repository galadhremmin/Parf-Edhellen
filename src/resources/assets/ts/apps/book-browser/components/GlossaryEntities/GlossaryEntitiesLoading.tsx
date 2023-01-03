import React from 'react';
import LoadingIndicator from '../LoadingIndicator';

function GlossaryEntitiesLoading({minHeight}: { minHeight: number }) {
    const heightStyle = {
        minHeight,
    };
    return <div style={heightStyle}>
        <LoadingIndicator text="Retrieving glossary..." />
    </div>;
}

export default GlossaryEntitiesLoading;
