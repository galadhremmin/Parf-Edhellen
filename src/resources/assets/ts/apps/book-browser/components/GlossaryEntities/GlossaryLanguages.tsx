import Spinner from '@root/components/Spinner';
import classNames from 'classnames';
import { Fragment, lazy, Suspense } from 'react';
import GlossaryLanguage from './GlossaryLanguage';
import type { IProps } from './GlossaryLanguages._types';

function GlossaryLanguages(props: IProps) {
    const {
        abstract,
        className,
        entityMorph,
        languages,
        sections,
        single,

        onReferenceClick,
    } = props;

    return <section className={classNames('ed-glossary', className || '', { 'ed-glossary--single': single})}>
        {abstract}
        {languages.map(
            (language) => <Fragment key={language.id}>
                <GlossaryLanguage language={language}
                    entries={sections[language.id]} onReferenceLinkClick={onReferenceClick} />
                {single && <section className="mt-3">
                    <Suspense fallback={<Spinner />}>
                        <DiscussAsync entityId={sections[language.id][0].latestLexicalEntryVersionId} entityType={entityMorph} prefetched={false} />
                    </Suspense>
                </section>}
            </Fragment>,
        )}
    </section>;
}

const DiscussAsync = lazy(() => import('@root/apps/discuss'));

export default GlossaryLanguages;
