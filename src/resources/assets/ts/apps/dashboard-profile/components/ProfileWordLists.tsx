import type { IProfileWordList, IProps } from './ProfileWordLists._types';

import './ProfileWordLists.scss';

/**
 * The word lists this person has published.
 *
 * Presented as link blocks rather than as a sentence among the statistics: a word list is somewhere
 * to go, and the few words shown underneath are what makes somebody want to go there.
 */
function ProfileWordLists({ nickname, wordLists }: IProps) {
    if (! Array.isArray(wordLists) || wordLists.length < 1) {
        return null;
    }

    return <div className="row ProfileWordLists">
        <div className="col-12">
            <h2>Word lists</h2>
            <p>
                Words {nickname} has collected and made public. Open one to read it, or turn it into
                a deck of flashcards and learn it.
            </p>
            <div className="link-blocks">
                {wordLists.map((wordList: IProfileWordList) => <blockquote key={wordList.id}>
                    <a className="block-link" href={wordList.url}>
                        <h3>{wordList.name}</h3>
                        <p>
                            {wordList.numberOfEntries === 1
                                ? '1 word'
                                : `${wordList.numberOfEntries} words`}
                        </p>
                        {wordList.description && <p>{wordList.description}</p>}
                        {wordList.previewWords?.length > 0 && <p className="ProfileWordLists--preview">
                            {wordList.previewWords.join(', ')}
                            {wordList.numberOfEntries > wordList.previewWords.length ? '…' : ''}
                        </p>}
                    </a>
                </blockquote>)}
            </div>
        </div>
    </div>;
}

export default ProfileWordLists;
