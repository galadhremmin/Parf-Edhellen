import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './FragmentsForm._types';

function FragmentsForm(props: IProps) {
    const {
        onChange,
        text,
    } = props;

    const _onChangeNative = useCallback((ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        const value = ev.target.value;
        fireEvent(null, onChange, value);
    }, [ onChange ]);

    return <>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-text-body">Text body</label>
            <textarea id="ed-sentence-text-body"
                      className="form-control"
                      onChange={_onChangeNative}
                      rows={10}
                      value={text}
            />
        </div>
    </>;
}

export default FragmentsForm;
