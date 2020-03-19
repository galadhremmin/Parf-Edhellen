import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { IProps } from './FragmentsForm._types';
import FragmentsGrid from './FragmentsGrid';

function FragmentsForm(props: IProps) {
    const {
        fragments,
        languageId,
        onFragmentChange,
        onParseTextRequest,
        onTextChange,
        text,
        textIsDirty,
    } = props;

    const _onChangeNative = useCallback((ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        const value = ev.target.value;
        fireEvent(null, onTextChange, value);
    }, [ onTextChange ]);

    const _onParseFragments = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent(null, onParseTextRequest, text);
    }, [ onParseTextRequest, text ]);

    return <>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-text-body">Text body</label>
            <textarea id="ed-sentence-text-body"
                      className="form-control"
                      onChange={_onChangeNative}
                      rows={10}
                      value={text}
            />
            <button className="btn btn-primary btn-block"
                    disabled={! textIsDirty}
                    onClick={_onParseFragments}>Update</button>
        </div>
        <FragmentsGrid fragments={fragments}
                       languageId={languageId}
                        onChange={onFragmentChange}
        />
    </>;
}

export default FragmentsForm;
