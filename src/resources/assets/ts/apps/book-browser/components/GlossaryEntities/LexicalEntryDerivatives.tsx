import classNames from '@root/utilities/ClassNames';
import type { IDerivativeNode } from '@root/connectors/backend/IBookApi';
import { DerivationStep, DerivationStepList } from './DerivationStepList';
import { useLanguageLookup } from './LanguageLookupContext';
import type { IProps } from './LexicalEntryDerivatives._types';

import './LexicalEntryDerivatives.scss';

/**
 * Renders the node itself, then its children as further siblings — a flat staircase list
 * (like LexicalEntryDerivations' ancestry chain) rather than a nested/collapsible tree, with
 * depth conveyed purely by indent. React fragments don't add a wrapper element, so the final
 * DOM is a single flat run of `<li>` under the top-level `<ul>`.
 */
function DerivativeNode(props: { node: IDerivativeNode; depth: number }) {
    const { depth, node } = props;
    const { getLanguage } = useLanguageLookup();
    const language = node.languageId ? getLanguage(node.languageId) : undefined;
    const displayWord = node.word || node.form;

    return <>
        <DerivationStep depth={depth}>
            {language && <span className="LexicalEntryDerivatives--language" title={language.name}>{(language.shortName || language.name).toLocaleUpperCase()}.</span>}
            {node.url
                ? <a className={classNames({ reconstructed: ! node.isWord })} href={node.url}>{displayWord}</a>
                : <span className={classNames({ reconstructed: ! node.isWord })}>{displayWord}</span>}
            {node.gloss && <span className="LexicalEntryDerivatives--gloss">{node.gloss}</span>}
            {node.source && <span className="LexicalEntryDerivatives--source">
                <span className="LexicalEntryDerivatives--source-icon">&#10022;</span> {node.source}
            </span>}
        </DerivationStep>
        {node.children.map((child) => <DerivativeNode
            key={child.lexicalEntryId ?? `${child.form}-${child.languageId}`}
            node={child} depth={depth + 1} />)}
    </>;
}

const LexicalEntryDerivatives = (props: IProps) => {
    const { derivatives } = props;

    if (! derivatives || derivatives.children.length < 1) {
        return null;
    }

    return <section className="GlossDetails details LexicalEntryDerivatives">
        <header>
            <h4>Derivatives</h4>
        </header>
        <div className="details__body">
            <DerivationStepList>
                {derivatives.children.map((child) => <DerivativeNode
                    key={child.lexicalEntryId ?? `${child.form}-${child.languageId}`}
                    node={child} depth={0} />)}
            </DerivationStepList>
            {derivatives.truncated && <p className="LexicalEntryDerivatives--truncated-note">
                This root has more derived words than shown here.
            </p>}
        </div>
    </section>;
};

export default LexicalEntryDerivatives;
