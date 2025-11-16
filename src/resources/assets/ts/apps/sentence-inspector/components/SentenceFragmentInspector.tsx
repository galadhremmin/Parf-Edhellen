import type { IComponentEvent } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import GlossInspector from '@root/apps/book-browser/components/GlossaryEntities/LexicalEntry';
import Markdown from '@root/components/Markdown';
import Quote from '@root/components/Quote';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import Tengwar from '@root/components/Tengwar';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import useLexicalEntry from '@root/utilities/hooks/useLexicalEntry';

import type { IProps } from './SentenceFragmentInspector._types';

function onReferenceLinkClick(ev: IComponentEvent<IReferenceLinkClickDetails>) {
    const globalEvents = resolve(DI.GlobalEvents);
    globalEvents.fire(globalEvents.loadReference, ev.value);
}

function SentenceFragmentInspector(props: IProps) {
    const {
        fragment,
    } = props;

    const { lexicalEntry, error } = useLexicalEntry(fragment?.lexicalEntryId, {
        adapter: (nextGloss) => ({
            ...nextGloss,
            _inflectedWord: {
                inflections: fragment.lexicalEntryInflections,
                speech: fragment.speech,
                word: fragment.fragment,
            },
        }),
    });

    return <article>
        <header>
            <h1><Tengwar transcribe={! fragment.tengwar} text={fragment.tengwar || fragment.fragment} /></h1>
        </header>
        {fragment.comments && <section className="abstract">
            <Markdown text={fragment.comments} parse={true} />
        </section>}
        <section>
            {(! lexicalEntry && ! error) && <Spinner />}
            {lexicalEntry && <GlossInspector
                bordered={false}
                lexicalEntry={lexicalEntry}
                onReferenceLinkClick={onReferenceLinkClick}
                toolbar={false}
                warnings={false}
            />}
            {error && <StaticAlert type="warning">
                <strong>Sorry, cannot find an lexical entry for <Quote>{fragment.fragment}</Quote>!</strong>{' '}
                This usually happens when the entry is deleted or outdated after the phrase was published. You can notify the author about this error alternatively contribute with a correction yourself.
            </StaticAlert>}
        </section>
    </article>;
}

export default SentenceFragmentInspector;
