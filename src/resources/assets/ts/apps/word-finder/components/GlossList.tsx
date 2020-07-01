import classNames from 'classnames';
import React, { useCallback } from 'react';
import { IGameGloss } from '../reducers/IGlossesReducer';

import './GlossList.scss';

function GlossList(props: { glosses: IGameGloss[]; }) {
    const {
        glosses,
    } = props;

    const _onWordOpen = useCallback(() => {
        // TODO: send signal to navigate to the gloss
    }, []);

    return <ul className="GlossList--glosses">
        {glosses.map((g) => <li key={g.gloss} className={classNames({ discovered: ! g.available })}>
            <span className="gloss">
                {`${g.gloss} (${g.wordForComparison.length})`}
            </span>
            {! g.available && <span className="word">
                <a href="#"
                    data-word-id={g.id}
                    onClick={_onWordOpen}
                    title={`Read more about ${g.word}.`}>
                    {g.word}
                </a>
            </span>}
        </li>)}
    </ul>;
}

export default GlossList;
