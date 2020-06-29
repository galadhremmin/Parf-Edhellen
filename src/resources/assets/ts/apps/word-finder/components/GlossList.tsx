import classNames from 'classnames';
import React from 'react';
import { IGameGloss } from '../reducers/IGlossesReducer';

import './GlossList.scss';

function GlossList(props: { glosses: IGameGloss[]; }) {
    const {
        glosses,
    } = props;

    return <ul className="GlossList--glosses">
        {glosses.map((g) => <li key={g.gloss} className={classNames({ discovered: ! g.available })}>
            <span className="gloss">
                {g.gloss}
            </span>
            {! g.available && <span className="word">{g.word}</span>}
        </li>)}
    </ul>;
}

export default GlossList;
