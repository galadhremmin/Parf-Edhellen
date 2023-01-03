import React from 'react';

function GlossaryEntitiesEmpty() {
    return <div>
        <h3>Alas! What you are looking for does not exist!</h3>
        <p>The word <em>{this.props.word}</em> does not exist in the dictionary.</p>
    </div>;
}

export default GlossaryEntitiesEmpty;
