import type { IComponentEvent } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import GlossInspector from '@root/apps/book-browser/components/GlossaryEntities/LexicalEntry';
import Markdown from '@root/components/Markdown';
import Quote from '@root/components/Quote';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import Tengwar from '@root/components/Tengwar';
import classNames from '@root/utilities/ClassNames';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import useLexicalEntry from '@root/utilities/hooks/useLexicalEntry';

import type { IProps } from './WordEntry._types';

const MutationsGroup = 'Mutations';

function onReferenceLinkClick(ev: IComponentEvent<IReferenceLinkClickDetails>) {
    const globalEvents = resolve(DI.GlobalEvents);
    globalEvents.fire(globalEvents.loadReference, ev.value);
}

/**
 * Everything known about one word, ranked by how well it is known.
 *
 * The three tiers are kept visibly apart on purpose. The form pair is derived from the
 * link itself and is therefore always true; the inflections and the note are an editor's
 * work and exist on a minority of words. Blurring the two would make the page claim more
 * than it can support -- so the pair is shown and the rule is never named unless somebody
 * named it.
 */
export default function WordEntry(props: IProps) {
    const {
        fragment,
        lineNumber,
        onSelectFragment,
        position,
        shape,
        totalCount,
        unopenedCount,
        unopenedFragmentId,
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

    const hasEditorial = fragment.lexicalEntryInflections.length > 0 || fragment.hasNote;

    return <article className="phrase-entry">
        <header>
            {(fragment.tengwar || fragment.fragment) && <p className="phrase-entry__tengwar" aria-hidden="true">
                <Tengwar transcribe={! fragment.tengwar} text={fragment.tengwar || fragment.fragment} />
            </p>}
            {/* h2, not h1: the phrase title above is the page's heading, and this is
                subordinate to it. */}
            <h2 className="phrase-entry__form">{fragment.fragment}</h2>
            {fragment.gloss && <p className="phrase-entry__gloss">{fragment.gloss}</p>}
            {fragment.speech && <p className="phrase-entry__speech ed-label">{fragment.speech}</p>}
            <p className="phrase-entry__where ed-ui">
                {shape === 'prose' ? 'Paragraph ' : 'Line '}{lineNumber}
                {' · word '}{position} of {totalCount}
            </p>
        </header>

        {fragment.isChanged && <section className="phrase-tier phrase-tier--derived">
            <p className="phrase-tier__title ed-label">The form you are reading</p>
            <p className="phrase-tier__pair">
                Written <em>{fragment.fragment}</em> — listed under <em>{fragment.headword}</em>
            </p>
            <p className="phrase-tier__provenance ed-ui">
                Derived from the entry this word links to.
            </p>
        </section>}

        {fragment.lexicalEntryInflections.length > 0 && <section className="phrase-tier phrase-tier--editor">
            <p className="phrase-tier__title ed-label">Annotated as</p>
            <div className="phrase-chips">
                {fragment.lexicalEntryInflections.map((inflection) => inflection.inflection
                    ? <span key={inflection.inflection.id}
                        title={inflection.inflection.groupName}
                        className={classNames('phrase-chip', {
                            'phrase-chip--mutation': inflection.inflection.groupName === MutationsGroup,
                        })}>
                        {inflection.inflection.name}
                    </span>
                    : null)}
            </div>
        </section>}

        {fragment.hasNote && <section className="phrase-tier phrase-tier--editor">
            <p className="phrase-tier__title ed-label">Note on this word</p>
            <Markdown text={fragment.comments} parse={true} />
        </section>}

        <section className="phrase-entry__gloss-body">
            {(! lexicalEntry && ! error) && <Spinner />}
            {lexicalEntry && <GlossInspector
                bordered={false}
                lexicalEntry={lexicalEntry}
                onReferenceLinkClick={onReferenceLinkClick}
                toolbar={true}
                warnings={false}
            />}
            {error && <StaticAlert type="warning">
                <strong>Sorry, cannot find an lexical entry for <Quote>{fragment.fragment}</Quote>!</strong>{' '}
                This usually happens when the entry is deleted or outdated after the phrase was published. You can notify the author about this error alternatively contribute with a correction yourself.
            </StaticAlert>}
        </section>

        <footer className="phrase-entry__foot">
            {fragment.source && <p className="ed-cite">{fragment.source}</p>}
            {fragment.collection && <p className="ed-ui">From the {fragment.collection} collection</p>}
        </footer>

        <div className="phrase-entry__nav">
            <button type="button"
                className="phrase-toggle"
                disabled={! fragment.previousFragmentId}
                onClick={() => onSelectFragment(fragment.previousFragmentId)}>
                &#8249; Previous
            </button>
            <button type="button"
                className="phrase-toggle"
                disabled={! fragment.nextFragmentId}
                onClick={() => onSelectFragment(fragment.nextFragmentId)}>
                Next &#8250;
            </button>
        </div>

        {unopenedFragmentId > 0 && <button type="button"
            className="phrase-toggle phrase-entry__unopened"
            onClick={() => onSelectFragment(unopenedFragmentId)}>
            Next unopened &#183; {unopenedCount} left
        </button>}

        <p className="ed-ui phrase-entry__hint">
            <span className="phrase-kbd">&#8592;</span> <span className="phrase-kbd">&#8594;</span> to move between words
        </p>
    </article>;
}
