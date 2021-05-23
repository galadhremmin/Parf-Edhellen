import React, { Suspense } from 'react';

import Markdown from '@root/components/Markdown';
import Spinner from '@root/components/Spinner';
import useGloss from '@root/utilities/hooks/useGloss';
import { IProps } from './GlossFragmentInspector._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { IComponentEvent } from '@root/components/Component._types';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';

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
            <h1>{fragment.fragment}</h1>
        </header>
        {fragment.comments && <section className="abstract">
            <Markdown text={fragment.comments} parse={true} />
        </section>}
        <section>
            {gloss && <Suspense fallback={<Spinner />}>
                <GlossInspectorAsync gloss={gloss}
                    onReferenceLinkClick={onReferenceLinkClick}
                    toolbar={false}
                    warnings={false}
                />
            </Suspense>}
            {! gloss && <Spinner />}
        </section>
    </article>;
}

const GlossInspectorAsync = React.lazy(() => import('@root/apps/book-browser/components/GlossaryEntities/Gloss'));

export default GlossFragmentInspector;
