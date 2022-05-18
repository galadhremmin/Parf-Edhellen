import classNames from 'classnames';
import React, {
    useCallback,
    useEffect,
    useRef,
} from 'react';

import { fireEvent } from '@root/components/Component';
import Quote from '@root/components/Quote';
import { IProps } from './CombinePartsStage._types';

import './CombinePartsStage.scss';
import { GameStage } from '../actions';

const getPartIdFromDataset = (target: EventTarget) => {
    const partIdAttribute = 'partId';
    const partId = parseInt((target as HTMLElement).dataset[partIdAttribute], 10);
    return partId;
}

function CombinePartsStage(props: IProps) {
    const {
        parts,
        selectedParts,
        tengwarMode,

        onChangeStage,
        onDeselectPart,
        onSelectPart,
    } = props;

    const partsRef = useRef<HTMLDivElement>();

    useEffect(() => {
        partsRef.current?.querySelector('button')?.focus();

        const availableParts = parts.filter((g) => g.available).length;
        if (availableParts < 1) {
            fireEvent('CombinePartsStage', onChangeStage, GameStage.Success);
        }
    }, [ parts ]);

    const _onDeselectPart = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent('PartList', onDeselectPart, getPartIdFromDataset(ev.target));
    }, [ onDeselectPart ]);

    const _onSelectPart = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent('CombinePartsStage', onSelectPart, getPartIdFromDataset(ev.target));
    }, [ onSelectPart ]);

    const _onUndo = useCallback(() => {
        if (! selectedParts.length) {
            return;
        }

        const partIndex = selectedParts[selectedParts.length - 1];
        const part = parts[partIndex];

        fireEvent('PartList', onDeselectPart, part.id);
    }, [ selectedParts, parts ]);

    return <div className="CombinePartsStage">
        <div className="CombinePartsStage__selected-parts">
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
                {parts.filter(p => p.available).map((p) => <button key={p.id}
                    className={classNames('btn btn-secondary', { 'disabled': p.selected })}
                    data-part-id={p.id}
                    onClick={_onSelectPart}>
                        {p.part.trim()}
                </button>)}
            </div>
            <div className={classNames('undo-button', { 'opacity-25': selectedParts.length === 0 })}>
                <button className="btn btn-secondary" onClick={_onUndo}>
                    {'Undo '}
                    {selectedParts.length > 0 && <Quote>{parts[selectedParts[selectedParts.length - 1]]?.part}</Quote>}
                </button>
            </div>
        </div>
        <div className="CombinePartsStage__tips">
            Are you stuck? <a href="#">Ask for a tip!</a>
        </div>
    </div>;
}

export default CombinePartsStage;
