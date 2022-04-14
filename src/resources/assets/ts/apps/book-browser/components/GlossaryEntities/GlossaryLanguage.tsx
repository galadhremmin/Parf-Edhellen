import React from 'react';

import Ad from '@root/apps/ad';
import Gloss from './Gloss';
import { IProps } from './GlossaryLanguage._types';
import Language from '../Language';

export default class GlossaryLanguage extends React.Component<IProps> {
    public render() {
        const {
            glosses,
            language,
            onReferenceLinkClick,
        } = this.props;

        return <article className="ed-glossary__language">
            <header>
                <Language language={language} />
            </header>
            <section className="ed-glossary__language__words">
                {glosses.map((gloss) => <Gloss gloss={gloss} key={gloss.id}
                    onReferenceLinkClick={onReferenceLinkClick} />)}
            </section>
            <section className="mt-3">
                <Ad ad="glossary" />
            </section>
        </article>;
    }
}
