
import { Fragment, useMemo } from 'react';

import type { IDerivationEntity, IPhoneticDevelopmentEntity } from '@root/connectors/backend/IBookApi';
import type { IProps, IGroupedRow } from './LexicalEntryPhoneticDevelopments._types';

import './LexicalEntryPhoneticDevelopments.scss';

/** The order-0 (immediate parent) derivation row per hypothesis, keyed by the groupUuid shared with its phonetic development chain. */
function primariesByGroupUuid(derivations: IDerivationEntity[][]): Map<string, IDerivationEntity> {
    const map = new Map<string, IDerivationEntity>();
    derivations.forEach((chain) => {
        const primary = chain.find((d) => d.order === 0) || chain[0];
        map.set(primary.groupUuid, primary);
    });
    return map;
}

/**
 * Eldamo records one entry per citation, not per unique sound-change — the same phonetic
 * development is frequently attested in several separate places (e.g. Let/426, PE17/025,
 * PE17/050 all cite the identical galadā > galadh development). Grouping by the composed
 * Development text and the stage/rule sequence collapses those into a single row with all of
 * their sources listed together, rather than repeating the same content once per citation.
 */
function groupByDevelopmentAndStages(
    phoneticDevelopments: IPhoneticDevelopmentEntity[][],
    primaries: Map<string, IDerivationEntity>,
    word: string,
): IGroupedRow[] {
    const groups = new Map<string, IGroupedRow>();

    for (const chain of phoneticDevelopments) {
        const primary = primaries.get(chain[0].groupUuid);

        // The "Development" summary traces the derivation's own recorded forms — its parent
        // form, any intermediate stages it recorded, and the dictionary's spelled headword (e.g.
        // "galadh") — not the phonetic chain's own last stage, which is a raw transliteration
        // (e.g. "galað") rather than the word's standard orthography.
        const developmentSteps = primary
            ? [primary.parentForm, ...(primary.intermediateStages || []), word]
            : [chain[0].word, word];

        const key = JSON.stringify(developmentSteps) + '::' + JSON.stringify(chain.map((s) => [s.word, s.rule]));

        const group = groups.get(key);
        if (group) {
            if (primary?.source) {
                group.sources.push(primary.source);
            }
        } else {
            groups.set(key, {
                chain,
                developmentSteps,
                sources: primary?.source ? [primary.source] : [],
            });
        }
    }

    return Array.from(groups.values());
}

function PhoneticDevelopmentRow(props: { row: IGroupedRow }) {
    const { row } = props;

    return <tr className="LexicalEntryPhoneticDevelopments--row">
        <td className="LexicalEntryPhoneticDevelopments--development">
            <em>*{row.developmentSteps.join(' > ')}</em>
        </td>
        <td className="LexicalEntryPhoneticDevelopments--stages">
            {row.chain.map((step, i) => <Fragment key={step.order}>
                {i > 0 && <span className="LexicalEntryPhoneticDevelopments--arrow">&gt;</span>}
                <span className="LexicalEntryPhoneticDevelopments--stage">
                    [{step.word}]
                    {i > 0 && step.rule && <span className="LexicalEntryPhoneticDevelopments--rule">({step.rule})</span>}
                </span>
            </Fragment>)}
        </td>
        <td className="LexicalEntryPhoneticDevelopments--sources">
            {row.sources.map((source) => <span key={source} className="LexicalEntryPhoneticDevelopments--source">
                <span className="LexicalEntryPhoneticDevelopments--source-icon">&#10022;</span> {source}
            </span>)}
        </td>
    </tr>;
}

const LexicalEntryPhoneticDevelopments = (props: IProps) => {
    const { derivations, phoneticDevelopments, word } = props;

    const rows = useMemo(() => {
        if (! phoneticDevelopments || phoneticDevelopments.length < 1) {
            return [];
        }

        const primaries = primariesByGroupUuid(derivations || []);
        return groupByDevelopmentAndStages(phoneticDevelopments, primaries, word);
    }, [derivations, phoneticDevelopments, word]);

    if (rows.length < 1) {
        return null;
    }

    return <section className="GlossDetails details LexicalEntryPhoneticDevelopments">
        <header>
            <h4>Phonetic Developments</h4>
        </header>
        <div className="details__body">
            <div className="table-responsive">
                <table className="table table-condensed table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Development</th>
                            <th>Stages</th>
                            <th>Sources</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => <PhoneticDevelopmentRow key={row.chain[0].groupUuid} row={row} />)}
                    </tbody>
                </table>
            </div>
        </div>
    </section>;
};

export default LexicalEntryPhoneticDevelopments;
