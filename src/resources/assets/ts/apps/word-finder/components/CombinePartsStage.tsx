import classNames from 'classnames';
import React, {
    useCallback,
    useState,
} from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './CombinePartsStage._types';

const getPartIdFromDataset = (target: EventTarget) => {
    const partIdAttribute = 'partId';
    const partId = parseInt((target as HTMLElement).dataset[partIdAttribute], 10);
    return partId;
}

function CombinePartsStage(props: IProps) {
    const {
        parts,
        selectedParts,

        onDeselectPart,
        onSelectPart,
    } = props;

    const _onDeselectPart = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent('PartList', onDeselectPart, getPartIdFromDataset(ev.target));
    }, [ onDeselectPart ]);

    const _onSelectPart = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent('CombinePartsStage', onSelectPart, getPartIdFromDataset(ev.target));
    }, [ onSelectPart ]);

    return <>
        <div>
            {selectedParts.map((i) => <a key={i}
                href="#"
                className=""
                onClick={_onDeselectPart}
                data-part-id={parts[i]?.id}>
                    {parts[i]?.part}
                </a>)}
        </div>
        <div>
            {parts.map((p, i) => <button key={p.id}
                className={classNames('btn btn-default', { 'hidden': ! p.available, 'disabled': p.selected })}
                data-part-id={p.id}
                onClick={_onSelectPart}>
                    {p.part}
            </button>)}
        </div>
    </>;
}

export default CombinePartsStage;
