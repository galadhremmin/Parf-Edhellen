import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './FragmentsForm._types';
import FragmentsGrid from './FragmentsGrid';

function FragmentsForm(props: IProps) {
    const {
        fragments,
        onFragmentChange,
        onTextChange,
        text,
    } = props;

    const _onChangeNative = useCallback((ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        const value = ev.target.value;
        fireEvent(null, onTextChange, value);
    }, [ onTextChange ]);

    return <>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-text-body">Text body</label>
            <textarea id="ed-sentence-text-body"
                      className="form-control"
                      onChange={_onChangeNative}
                      rows={10}
                      value={text}
            />
            <FragmentsGrid fragments={fragments}
                           onChange={onFragmentChange}
            />
        </div>
    </>;
}

export default FragmentsForm;
