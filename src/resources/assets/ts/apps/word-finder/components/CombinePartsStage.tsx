import classNames from '@root/utilities/ClassNames';
import {
    useCallback,
    useEffect,
    useRef,
} from 'react';
import type { CSSProperties, MouseEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import Quote from '@root/components/Quote';
import type { IProps } from './CombinePartsStage._types';
import { GameStage } from '../actions';

import './CombinePartsStage.scss';

const getPartIdFromDataset = (target: EventTarget) => {
    const partIdAttribute = 'partId';
    const partId = parseInt((target as HTMLElement).dataset[partIdAttribute], 10);
    return partId;
}

function CombinePartsStage(props: IProps) {
    const {
        parts,
        selectedParts,
        hintPartId,
        hintsRemaining,
        rejectFragmentKey,

        onChangeStage,
        onDeselectPart,
        onHint,
        onSelectPart,
    } = props;

    const partsRef = useRef<HTMLDivElement>();
    const undoButtonRef = useRef<HTMLButtonElement | null>(null);
    const selectedPartsRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!rejectFragmentKey) return;
        const el = selectedPartsRef.current;
        if (!el) return;
        el.classList.remove('shaking');
        void el.offsetWidth;
        el.classList.add('shaking');
    }, [ rejectFragmentKey ]);

    useEffect(() => {
        partsRef.current?.querySelector('button')?.focus();

        const availableParts = parts.filter((g) => g.available).length;
        if (availableParts < 1) {
            void fireEvent('CombinePartsStage', onChangeStage, GameStage.Success);
        }
    }, [ parts ]);

    const _onDeselectPart = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void fireEvent('PartList', onDeselectPart, getPartIdFromDataset(ev.target));
    }, [ onDeselectPart ]);

    const _onSelectPart = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        void fireEvent('CombinePartsStage', onSelectPart, getPartIdFromDataset(ev.target));
    }, [ onSelectPart ]);

    const _onUndo = useCallback(() => {
        if (! selectedParts.length) {
            const btn = undoButtonRef.current;
            if (btn) {
                btn.classList.remove('shaking');
                void btn.offsetWidth; // force reflow to re-trigger animation
                btn.classList.add('shaking');
            }
            return;
        }

        const partIndex = selectedParts[selectedParts.length - 1];
        const part = parts[partIndex];

        void fireEvent('PartList', onDeselectPart, part.id);
    }, [ selectedParts, parts ]);

    return <div className="CombinePartsStage">
        <div className="CombinePartsStage__selected-parts" ref={selectedPartsRef}>
            {selectedParts.length ? selectedParts.map((i) => <a key={i}
                href="#"
                className=""
                onClick={_onDeselectPart}
                data-part-id={parts[i]?.id}>
                    {parts[i]?.part}
                </a>) : <span className="info">
                    Select your first set letters below...
                </span>}
        </div>
        <div className="CombinePartsStage__parts">
            <div className="choices" ref={partsRef}>
                {parts.filter(p => p.available).map((p, index) => <button key={p.id}
                    className={classNames('btn btn-secondary', { 'disabled': p.selected, 'hint': p.id === hintPartId })}
                    data-part-id={p.id}
                    onClick={_onSelectPart}
                    style={{ '--tile-delay': `${index * 0.04}s` } as CSSProperties}>
                        {p.part.trim()}
                </button>)}
            </div>
            <div className="action-row">
                <div className="undo-button">
                    <button className={classNames('btn btn-secondary', { 'opacity-25': selectedParts.length === 0 })}
                        ref={undoButtonRef}
                        onClick={_onUndo}>
                        {'← Undo '}
                        {selectedParts.length > 0 && <Quote>{parts[selectedParts[selectedParts.length - 1]]?.part}</Quote>}
                    </button>
                </div>
                <div className="hint-button">
                    {hintsRemaining > 0
                        ? <button className="btn btn-outline-secondary" onClick={onHint}>
                            Hint ({hintsRemaining} left)
                          </button>
                        : <button className="btn btn-outline-secondary" disabled>
                            No hints left
                          </button>}
                </div>
            </div>
        </div>
    </div>;
}

export default CombinePartsStage;
