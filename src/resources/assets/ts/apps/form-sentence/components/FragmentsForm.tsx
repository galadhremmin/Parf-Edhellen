import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { CommonPaths } from '@root/config';
import { IProps } from './FragmentsForm._types';
import FragmentsGrid from './FragmentsGrid';
import LanguageAlert from './LanguageAlert';

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

    if (! languageId) {
        return <LanguageAlert />;
    }

    return <>
        <p>
            Use the text field below to write and press <kbd>Update text body</kbd> when your text
            is done. Once you have pressed the button, your text will be divided into sentences and words.
            You will need to link each word to a gloss. If a gloss does not currently exist in the dictionary,
            you can <a href={CommonPaths.contributions.gloss} target="_blank">publish the gloss as a separate contribution</a>{' '}
            <em>(opens in a new tab)</em>.
        </p>
        <div className="form-group">
            <label htmlFor="ed-sentence-text-body">Text body</label>
            <textarea id="ed-sentence-text-body"
                      className="form-control"
                      onChange={_onChangeNative}
                      rows={10}
                      value={text}
            />
            <button className="btn btn-primary btn-block"
                    disabled={! textIsDirty}
                    onClick={_onParseFragments}>Update text body</button>
        </div>
        <hr />
        <FragmentsGrid
            fragments={fragments}
            languageId={languageId}
            onChange={onFragmentChange}
        />
    </>;
}

export default FragmentsForm;
