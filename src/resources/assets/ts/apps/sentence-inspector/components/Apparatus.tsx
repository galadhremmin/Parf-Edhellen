import { useCallback } from 'react';

import classNames from '@root/utilities/ClassNames';

import type { ApparatusTab, IProps } from './Apparatus._types';

import './Apparatus.scss';

/**
 * The apparatus: beside the text on a wide screen, a sheet at the foot of a narrow one.
 *
 * It replaces a fixed panel that covered 55vh, locked the body's scroll and jumped the
 * page to the selected word on every render. Reading is a loop of small clicks, and each
 * of those cost the reader their place.
 *
 * Collapsed, the sheet is sized by height rather than translated down by its own size:
 * a translate leaves the handle in the bottom few rem of the viewport, which is exactly
 * where a browser's own toolbar sits. Everything visible while collapsed opens it.
 */
export default function Apparatus(props: IProps) {
    const {
        entry,
        handleText,
        index,
        isOpen,
        onOpenChange,
        onTabChange,
        tab,
    } = props;

    const _onHandleClick = useCallback(() => onOpenChange(! isOpen), [ isOpen, onOpenChange ]);

    const _onTabClick = useCallback((nextTab: ApparatusTab) => {
        onTabChange(nextTab);
        onOpenChange(true);
    }, [ onOpenChange, onTabChange ]);

    return <aside className={classNames('phrase-apparatus', { 'is-open': isOpen })}>
        <button type="button"
            className="phrase-apparatus__handle"
            aria-expanded={isOpen}
            onClick={_onHandleClick}>
            <span>{handleText}</span>
            <span className="phrase-apparatus__handle-state">{isOpen ? 'Hide ▾' : 'Open ▴'}</span>
        </button>

        <div className="phrase-apparatus__tabs" role="tablist">
            <button type="button"
                role="tab"
                className="phrase-tab"
                aria-selected={tab === 'index'}
                onClick={() => _onTabClick('index')}>
                Word by word
            </button>
            <button type="button"
                role="tab"
                className="phrase-tab"
                aria-selected={tab === 'entry'}
                onClick={() => _onTabClick('entry')}>
                Selected word
            </button>
        </div>

        <div className="phrase-apparatus__panel" role="tabpanel" hidden={tab !== 'index'}>
            {index}
        </div>
        <div className="phrase-apparatus__panel" role="tabpanel" hidden={tab !== 'entry'}>
            {entry}
        </div>
    </aside>;
}
