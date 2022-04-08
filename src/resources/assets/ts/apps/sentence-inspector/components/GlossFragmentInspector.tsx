import React, { Suspense } from 'react';

import Markdown from '@root/components/Markdown';
import Spinner from '@root/components/Spinner';
import useGloss from '@root/utilities/hooks/useGloss';
import { IProps } from './GlossFragmentInspector._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { IComponentEvent } from '@root/components/Component._types';
import Quote from '@root/components/Quote';
import StaticAlert from '@root/components/StaticAlert';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import Tengwar from '@root/components/Tengwar';

function onReferenceLinkClick(ev: IComponentEvent<IReferenceLinkClickDetails>) {
    const globalEvents = new GlobalEventConnector();
    globalEvents.fire(globalEvents.loadReference, ev.value);
}

function GlossFragmentInspector(props: IProps) {
    const {
        fragment,
    } = props;

    const gloss = useGloss(fragment?.glossId, (nextGloss) => ({
        ...nextGloss,
        inflectedWord: {
            inflections: fragment.inflections,
            speech: fragment.speech,
            word: fragment.fragment,
        },
    }));

    return <article>
        <header>
            <h1><Tengwar transcribe={!!! fragment.tengwar} text={fragment.tengwar || fragment.fragment} /></h1>
        </header>
        {fragment.comments && <section className="abstract">
            <Markdown text={fragment.comments} parse={true} />
        </section>}
        <section>
            {gloss && <Suspense fallback={<Spinner />}>
                {gloss.error === null && <GlossInspectorAsync gloss={gloss?.gloss}
                    onReferenceLinkClick={onReferenceLinkClick}
                    toolbar={false}
                    warnings={false}
                />}
                {gloss.error !== null && <StaticAlert type="warning">
                    <strong>Sorry, cannot find a gloss for <Quote>{fragment.fragment}</Quote>!</strong>{' '}
                    This usually happens when the gloss is deleted or outdated after the phrase was published. You can notify the author about this error alternatively contribute with a correction yourself.
                </StaticAlert>}
            </Suspense>}
            {! gloss && <Spinner />}
        </section>
    </article>;
}

const GlossInspectorAsync = React.lazy(() => import('@root/apps/book-browser/components/GlossaryEntities/Gloss'));

export default GlossFragmentInspector;
