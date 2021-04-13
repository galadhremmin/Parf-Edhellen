import React from 'react';

import Ad from '@root/apps/ad/containers/Ad';
import Language from '../Language';
import { IProps } from './Sentences._types';
import Sentence from './Sentence';

function Sentences(props: IProps) {
    const {
        language,
        sentences,
    } = props;

    return <article className="ed-glossary__language">
        <header>
            <Language language={language} />
        </header>
        <section className="link-blocks">
            {sentences.map((sentence) => <Sentence key={sentence.id} sentence={sentence} />)}
        </section>
        <section>
            <Ad ad="glossary" />
        </section>
    </article>;
}

export default Sentences;
