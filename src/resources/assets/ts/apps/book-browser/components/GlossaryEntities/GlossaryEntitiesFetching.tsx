import Spinner from '@root/components/Spinner';

import './GlossaryEntitiesLoading.scss';

/**
 * Shown over the glossary already on screen while the next one is fetched.
 *
 * The layout of a glossary depends on how many languages and entries come back,
 * which is precisely what we are waiting to find out. So rather than replace
 * the page with a guess at its shape, the outgoing glossary stays exactly where
 * it is, dimmed, and this floats above it to say which word is on its way.
 * Nothing moves until there is something real to move to.
 */
function GlossaryEntitiesFetching({ word }: { word?: string }) {
    return <div className="GlossaryEntitiesFetching" role="status" aria-live="polite">
        <div className="GlossaryEntitiesFetching__card">
            <Spinner />
            {word
                ? <span className="GlossaryEntitiesFetching__word">{word}</span>
                : <span className="GlossaryEntitiesFetching__word">Retrieving glossary</span>}
            <span className="GlossaryEntitiesFetching__hint">looking it up&hellip;</span>
        </div>
    </div>;
}

export default GlossaryEntitiesFetching;
