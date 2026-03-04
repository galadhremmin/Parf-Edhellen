import classNames from '@root/utilities/ClassNames';
import { useCallback } from 'react';
import type { MouseEvent } from 'react';

import Quote from '@root/components/Quote';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import type { IProps } from './GlossList._types';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import './GlossList.scss';

function GlossList(props: IProps) {
    const {
        glosses,
        tengwarMode,
        globalEvents,
    } = props;

    const _onWordOpen = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const lexicalEntryIdAttribute = 'lexicalEntryId';
        const lexicalEntryId = parseInt((ev.target as HTMLAnchorElement).dataset[lexicalEntryIdAttribute], 10);
        
        globalEvents?.fire(globalEvents.loadReference, { lexicalEntryId });
    }, []);

    return <ul className="GlossList--glosses">
        {glosses.map((g) => <li key={g.gloss} className={classNames({ discovered: ! g.available })}>
            <span className="bullet">
                {g.available
                    ? <TextIcon icon="question" className="icon-unknown" />
                    : <TextIcon icon="ok" className="icon-discovered" />}
            </span>
            {! g.available && <span className="word">
                <Quote>
                    <a href="#"
                        data-lexical-entry-id={g.id}
                        onClick={_onWordOpen}
                        title={`Read more about ${g.word}.`}>
                        {g.word}
                    </a>
                </Quote>
                {' '}
                {!! tengwarMode && <Tengwar mode={tengwarMode} transcribe={true} text={g.word} />}
                {': '}
            </span>}
            <span className="gloss">
                {g.gloss}{' '}
                {g.available && <span className="letter-slots" aria-label={`${g.wordLength} letters`}>
                    {Array.from({ length: g.wordLength }, (_, i) => <span key={i} className="slot" />)}
                </span>}
            </span>
        </li>)}
    </ul>;
}

export default withPropInjection(GlossList, {
    globalEvents: DI.GlobalEvents,
});
