import { useCallback } from 'react';
import type { ChangeEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import { FeedFormat } from '@root/connectors/IFeedApi';

import type { IProps } from './FeedFormatSelect._types';

function FeedFormatSelect(props: IProps) {
    const {
        id,
        name,
        onChange,
        value,
    } = props;

    const values = Object.keys(FeedFormat).map((text) => ({
        text,
        value: (FeedFormat as Record<string, string>)[text], // TODO - there has to be a more elegant way to do this.
    }));

    const _onChange = useCallback((ev: ChangeEvent<HTMLSelectElement>) => {
        void fireEvent(id || name, onChange, ev.target.value);
    }, [ id, name, onChange ]);

    return <select className="form-control"
                   id={id}
                   name={name}
                   onChange={_onChange}
                   value={value}>
        {values.map((p) => <option value={p.value} key={p.value}>{p.text}</option>)}
    </select>;
}

export default FeedFormatSelect;
