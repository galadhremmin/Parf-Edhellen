import type { CSSProperties } from 'react';

import classNames from '@root/utilities/ClassNames';
import type { IListProps, IStepProps } from './DerivationStepList._types';

import './DerivationStepList.scss';

/**
 * Shared list mechanics for both LexicalEntryDerivations (ancestry, "⇐") and
 * LexicalEntryDerivatives (descendants, "⇒") — a flat staircase of steps indented by
 * `depth`, one `<li>` per step. Each caller supplies its own step content as children and
 * scopes its own arrow direction/placement via a descendant selector on `--step`, e.g.
 * `.LexicalEntryDerivations .DerivationStepList--step::before { content: "⇐ "; }`.
 */
export function DerivationStepList(props: IListProps) {
    return <ul className="DerivationStepList">{props.children}</ul>;
}

export function DerivationStep(props: IStepProps) {
    const { children, className, depth } = props;

    return <li className={classNames('DerivationStepList--step', className)}
        style={{ '--ed-derivation-depth': depth } as CSSProperties}>
        {children}
    </li>;
}
