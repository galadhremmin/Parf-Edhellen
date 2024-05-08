import Ad from '@root/apps/ad';
import Language from '../Language';
import Gloss from './Gloss';
import { IProps } from './GlossaryLanguage._types';

export default function GlossaryLanguage(props: IProps) {
    const {
        glosses,
        language,
        onReferenceLinkClick,
    } = props;

    return <article className="ed-glossary__language">
        <header>
            <Language language={language} />
        </header>
        <section className="ed-glossary__language__words">
            {glosses.map((gloss) => <Gloss gloss={gloss} key={gloss.id}
                toolbar={true} onReferenceLinkClick={onReferenceLinkClick} />)}
        </section>
        <section className="mt-3">
            <Ad ad="glossary" />
        </section>
    </article>;
}
