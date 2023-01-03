import React from 'react';

function GlossaryEntitiesEmpty({ word }: { word: string}) {
    return <div>
        <h3>Alas! What you are looking for does not exist!</h3>
        <p>The word <em>{word}</em> does not exist in the dictionary.</p>
    </div>;
}

export default GlossaryEntitiesEmpty;
