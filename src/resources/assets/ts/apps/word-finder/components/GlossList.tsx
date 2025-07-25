import classNames from 'classnames';
import React, { useCallback } from 'react';

import Quote from '@root/components/Quote';
import Tengwar from '@root/components/Tengwar';
import { IProps } from './GlossList._types';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import './GlossList.scss';

function GlossList(props: IProps) {
    const {
        glosses,
        tengwarMode,
        globalEvents,
    } = props;

    const _onWordOpen = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const lexicalEntryIdAttribute = 'lexicalEntryId';
        const lexicalEntryId = parseInt((ev.target as HTMLAnchorElement).dataset[lexicalEntryIdAttribute], 10);
        
        globalEvents?.fire(globalEvents.loadReference, { lexicalEntryId });
    }, []);

    return <ul className="GlossList--glosses">
        {glosses.map((g) => <li key={g.gloss} className={classNames({ discovered: ! g.available })}>
            {! g.available && <span className="word">
                <Quote>
                    <a href="#"
                        data-gloss-id={g.id}
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
                {g.available && `(${g.wordLength} letters)`}
            </span>
        </li>)}
    </ul>;
}

export default withPropInjection(GlossList, {
    globalEvents: DI.GlobalEvents,
});
