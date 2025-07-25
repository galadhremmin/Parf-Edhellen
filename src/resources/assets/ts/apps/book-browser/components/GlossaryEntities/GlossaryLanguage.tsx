import Ad from '@root/apps/ad';
import Language from '../Language';
import LexicalEntry from './LexicalEntry';
import { IProps } from './GlossaryLanguage._types';

export default function GlossaryLanguage(props: IProps) {
    const {
        entries,
        language,
        onReferenceLinkClick,
    } = props;

    return <article className="ed-glossary__language">
        <header>
            <Language language={language} />
        </header>
        <section className="ed-glossary__language__words">
            {entries.map((entry) => <LexicalEntry lexicalEntry={entry} key={entry.id}
                toolbar={true} onReferenceLinkClick={onReferenceLinkClick} />)}
        </section>
        <section className="mt-3">
            <Ad ad="glossary" />
        </section>
    </article>;
}
