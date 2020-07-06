import classNames from 'classnames';
import React, { useCallback } from 'react';

import Quote from '@root/components/Quote';
import Tengwar from '@root/components/Tengwar';
import { IProps } from './GlossList._types';

import './GlossList.scss';

function GlossList(props: IProps) {
    const {
        glosses,
        tengwarMode,
    } = props;

    const _onWordOpen = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const glossIdAttribute = 'glossId';
        const glossId = parseInt((ev.target as HTMLAnchorElement).dataset[glossIdAttribute], 10);
        console.log(glossId);
        // TODO: navigate to gloss
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

export default GlossList;
