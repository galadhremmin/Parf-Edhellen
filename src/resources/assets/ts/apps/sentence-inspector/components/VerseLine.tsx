import type { ILine, IToken } from '../reducers/LinesReducer._types';
import type { IProps } from './VerseLine._types';

import classNames from '@root/utilities/ClassNames';
import WordButton from './WordButton';

/**
 * One line of verse: the tengwar, the transcription beneath it, and the translation
 * subordinate to both -- a critical edition's stanza, which is what this is.
 */
export default function VerseLine(props: IProps) {
    const {
        display,
        fragments,
        isActive,
        line,
        onSelectFragment,
        selectedFragmentId,
        trail,
    } = props;

    const renderTokens = (tokens: IToken[], plain: boolean, keyPrefix: string) =>
        tokens.map((token, i) => {
            const fragment = token.fragmentId ? fragments.get(token.fragmentId) : null;
            if (! fragment) {
                return <span key={`${keyPrefix}-${i}`} className="phrase-sep">{token.text}</span>;
            }

            return <WordButton key={`${keyPrefix}-${i}`}
                fragment={fragment}
                highlightChanged={display.changedForms}
                isOpened={trail.has(fragment.id)}
                isSelected={fragment.id === selectedFragmentId}
                onClick={onSelectFragment}
                plain={plain}
                text={token.text}
            />;
        });

    return <div className={classNames('phrase-line', { 'is-active': isActive })}
        data-line={line.n}>
        <div className="phrase-line__n" aria-hidden="true">{line.n}</div>
        <div className="phrase-line__body">
            {display.tengwar && <p className="phrase-line__tengwar tengwar">
                {renderTokens(line.tengwar, true, 't')}
            </p>}
            {display.latin && <p className="phrase-line__latin">
                {renderTokens(line.latin, false, 'l')}
            </p>}
            {display.translation && line.translation &&
                <p className="phrase-line__translation">{line.translation}</p>}
        </div>
    </div>;
}
