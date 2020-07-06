import classNames from 'classnames';
import React, {
    useCallback,
    useEffect,
    useRef,
} from 'react';

import { fireEvent } from '@root/components/Component';
import Tengwar from '@root/components/Tengwar';
import { IProps } from './CombinePartsStage._types';

import './CombinePartsStage.scss';
import Spinner from '@root/components/Spinner';
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

    return <div className="CombinePartsStage">
        <div className="CombinePartsStage__selected-parts">
            {selectedParts.length ? selectedParts.map((i) => <a key={i}
                href="#"
                className=""
                onClick={_onDeselectPart}
                data-part-id={parts[i]?.id}>
                    {parts[i]?.part}
                </a>) : <span className="info">
                    Select your initial letters...
                </span>}
        </div>
        <div className="CombinePartsStage__parts" ref={partsRef}>
            {parts.map((p) => <button key={p.id}
                className={classNames('btn btn-default', { 'hidden': ! p.available, 'disabled': p.selected })}
                data-part-id={p.id}
                onClick={_onSelectPart}>
                    {`${p.part} `}
                    {!! tengwarMode && <Tengwar mode="sindarin" transcribe={true} text={p.part} />}
            </button>)}
        </div>
        <div className="CombinePartsStage__tips">
            Are you stuck? <a href="#">Ask for a tip!</a>
        </div>
    </div>;
}

export default CombinePartsStage;
