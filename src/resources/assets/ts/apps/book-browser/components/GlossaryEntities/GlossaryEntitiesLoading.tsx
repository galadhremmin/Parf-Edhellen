import Spinner from '@root/components/Spinner';

import LoadingIndicator from '../LoadingIndicator';

import './GlossaryEntitiesLoading.scss';

/**
 * The very first glossary of a session, where there is nothing on screen yet to
 * keep in place.
 *
 * Deliberately makes no attempt to imitate the glossary that follows: its shape
 * is decided by how many languages and entries the answer contains, so a
 * placeholder version of it would be a guess, and a wrong guess reads as the
 * page jumping. One composed state that is honestly a loading state, naming the
 * word that was asked for, is steadier than a bad impression of the answer.
 *
 * Once a glossary is on screen, GlossaryEntitiesFetching takes over and nothing
 * is replaced at all.
 */
function GlossaryEntitiesLoading({ minHeight, word }: { minHeight: number; word?: string }) {
    // No word to name: a deep link or a history navigation, where the word is
    // not known until the response lands.
    if (! word) {
        return <div style={{ minHeight }}>
            <LoadingIndicator text="Retrieving glossary..." />
        </div>;
    }

    return <div className="GlossaryEntitiesLoading" style={{ minHeight }} aria-busy="true">
        <Spinner />
        <p className="GlossaryEntitiesLoading__word">{word}</p>
        <span className="GlossaryEntitiesLoading__rule" aria-hidden="true"></span>
        <p className="GlossaryEntitiesLoading__status" role="status">
            Looking up <strong>{word}</strong> in the dictionary&hellip;
        </p>
    </div>;
}

export default GlossaryEntitiesLoading;
