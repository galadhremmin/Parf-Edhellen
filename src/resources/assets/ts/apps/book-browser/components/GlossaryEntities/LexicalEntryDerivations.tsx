
import { useMemo } from 'react';
import type { ReactNode } from 'react';

import classNames from '@root/utilities/ClassNames';
import TextIcon from '@root/components/TextIcon';
import type { IDerivationEntity } from '@root/connectors/backend/IBookApi';
import { DerivationStep, DerivationStepList } from './DerivationStepList';
import { useLanguageLookup } from './LanguageLookupContext';
import RootForm from './RootForm';
import type { IProps, IGroupedChain, ICitation } from './LexicalEntryDerivations._types';

import './LexicalEntryDerivations.scss';

/**
 * Eldamo records one hypothesis per citation, not per unique etymology — the same ancestry is
 * frequently attested in several separate places (see LexicalEntryPhoneticDevelopments.tsx for
 * the same pattern). Grouping by each step's resolved identity — language and resolved lexical
 * entry (or unresolved form+language), plus uncertain/rejected flags — collapses those into a
 * single chain. The per-citation spelling and intermediate stages are citation-specific, not
 * chain-identity, so they're kept on each citation instead of the grouping key.
 */
function groupByChainContent(derivations: IDerivationEntity[][]): IGroupedChain[] {
    const groups = new Map<string, IGroupedChain>();

    for (const chain of derivations) {
        const [primary] = chain;
        const citation: ICitation = {
            comment: primary.comment,
            intermediateStages: primary.intermediateStages,
            parentForm: primary.parentForm,
            source: primary.source,
        };

        const key = JSON.stringify(chain.map((step) => [
            step.parentLanguageId, step.parentLexicalEntryId, step.isUncertain, step.isRejected,
        ]));

        const group = groups.get(key);
        if (group) {
            if (citation.source || citation.comment) {
                group.citations.push(citation);
            }
        } else {
            groups.set(key, {
                chain,
                citations: citation.source || citation.comment ? [citation] : [],
            });
        }
    }

    return Array.from(groups.values());
}

/** Non-root ancestor: bare word, styling-wise — the language badge is rendered by AncestorStep. */
function LanguagedWord(props: {
    displayWord: string;
    isReconstructed: boolean;
    url: string | null;
}) {
    const { displayWord, isReconstructed, url } = props;

    return url
        ? <a className={classNames({ reconstructed: isReconstructed })} href={url}>{displayWord}</a>
        : <span className={classNames({ reconstructed: isReconstructed })}>{displayWord}</span>;
}

function AncestorStep(props: {
    step: IDerivationEntity;
    depth: number;
    note?: ReactNode;
    intermediateStagesNode?: ReactNode;
}) {
    const { depth, intermediateStagesNode, note, step } = props;
    const { getLanguage } = useLanguageLookup();
    const isReconstructed = step.parentLabel === 'Reconstructed';
    // Roots are cited in their own uppercase spelling (parentForm), never the resolved entry's
    // plain-cased Word record — see RootForm and BookAdapter::adaptDerivations() for why.
    const displayWord = step.parentIsRoot ? step.parentForm : (step.parentWord || step.parentForm);
    const language = step.parentLanguageId ? getLanguage(step.parentLanguageId) : undefined;

    return <DerivationStep depth={depth} className={classNames({ rejected: step.isRejected })}>
        {language && <span className="LexicalEntryDerivations--language" title={language.name}>{(language.shortName || language.name).toLocaleUpperCase()}.</span>}
        {step.parentIsRoot
            ? <RootForm form={displayWord} languageId={step.parentLanguageId} url={step.parentUrl} />
            : <LanguagedWord displayWord={displayWord} isReconstructed={isReconstructed} url={step.parentUrl} />}
        {step.parentGloss && <span className="LexicalEntryDerivations--gloss">{step.parentGloss}</span>}
        {step.isUncertain && <span className="uncertain" title="Uncertain etymology">
            <TextIcon icon="asterisk" />
        </span>}
        {step.isRejected && <span className="LexicalEntryDerivations--rejected-label" title="Etymology later abandoned by Tolkien">
            rejected
        </span>}
        {note}
        {intermediateStagesNode}
    </DerivationStep>;
}

/**
 * Renders a citation's own intermediate spelling waypoints as a nested chain ending at the
 * citation's cited form, e.g. "malþorn < malt-ornē" — plain text, since these aren't resolved
 * lexical entries of their own. Unlike the main ancestry staircase, this is always a single
 * inline row with no depth semantics (Derivatives has no equivalent), so it doesn't use the
 * shared DerivationStepList — sharing it caused every waypoint list's first entry to carry
 * --ed-derivation-depth: 0, indistinguishable from a genuine top-level ancestry root.
 */
function IntermediateStages(props: { citation: ICitation }) {
    const { citation } = props;
    const steps = [...(citation.intermediateStages || []), citation.parentForm];

    return <li className="LexicalEntryDerivations--intermediate-chain">
        <ul className="LexicalEntryDerivations--stage-list">
            {steps.map((form, i) => <li key={i} className="LexicalEntryDerivations--stage">
                <span>{form}</span>
            </li>)}
        </ul>
        {citation.source && <span className="LexicalEntryDerivations--note">{citation.source}</span>}
    </li>;
}

/**
 * Renders one hypothesis's steps as flat siblings — no wrapping "chain" element — matching
 * LexicalEntryDerivatives' single flat list. The citation note and intermediate-stages block
 * both derive from the chain's order-0 row (see groupByChainContent), so they attach to that
 * same first step's `<li>`, exactly where LexicalEntryDerivatives attaches source/gloss to the
 * node they describe, rather than trailing the whole hypothesis as a separate sibling.
 */
function DerivationGroup(props: { group: IGroupedChain }) {
    const { group } = props;
    const [primary] = group.chain;
    const stagedCitations = group.citations.filter((citation) => citation.intermediateStages && citation.intermediateStages.length > 0);

    const note = group.citations.length > 0 && <span className="LexicalEntryDerivations--note">
        {group.citations.map((citation, i) => <span key={i}>
            {i > 0 && '; '}
            {citation.source}
            {citation.source && primary.parentWord && citation.parentForm !== primary.parentWord && ` (${citation.parentForm})`}
            {citation.source && citation.comment && ': '}
            {citation.comment}
        </span>)}
    </span>;

    const intermediateStagesNode = stagedCitations.length > 0 && <ul className="LexicalEntryDerivations--intermediate-stages">
        {stagedCitations.map((citation, i) => <IntermediateStages key={i} citation={citation} />)}
    </ul>;

    return <>
        {/* A chain step's own order isn't always unique — the same depth can hold two
            branching ancestors (a parser quirk noted in the etymology data) — so key on
            position, not order, to avoid React conflating two distinct steps. */}
        {group.chain.map((step, i) => <AncestorStep key={i} step={step} depth={i}
            note={i === 0 ? note : undefined}
            intermediateStagesNode={i === 0 ? intermediateStagesNode : undefined} />)}
    </>;
}

const LexicalEntryDerivations = (props: IProps) => {
    const { derivations } = props;

    const groups = useMemo(
        () => (derivations && derivations.length > 0 ? groupByChainContent(derivations) : []),
        [derivations],
    );

    if (groups.length < 1) {
        return null;
    }

    return <section className="GlossDetails details LexicalEntryDerivations">
        <header>
            <h4>Derivations</h4>
        </header>
        <div className="details__body">
            <DerivationStepList>
                {groups.map((group) => <DerivationGroup key={group.chain[0].groupUuid} group={group} />)}
            </DerivationStepList>
        </div>
    </section>;
};

export default LexicalEntryDerivations;
