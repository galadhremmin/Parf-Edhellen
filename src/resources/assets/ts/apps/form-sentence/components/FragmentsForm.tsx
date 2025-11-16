import { useCallback } from 'react';
import type { ChangeEvent, MouseEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import { CommonPaths } from '@root/config';
import type { IProps } from './FragmentsForm._types';
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

    const _onChangeNative = useCallback((ev: ChangeEvent<HTMLTextAreaElement>) => {
        const value = ev.target.value;
        void fireEvent(null, onTextChange, value);
    }, [ onTextChange ]);

    const _onParseFragments = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        void fireEvent(null, onParseTextRequest, text);
    }, [ onParseTextRequest, text ]);

    if (! languageId) {
        return <LanguageAlert />;
    }

    return <>
        <p>
            Use the text field below to write and press <kbd>Update</kbd> when your text
            is done. Once you have pressed the button, your text will be divided into sentences and words.
            You will need to link each word to a gloss. If a gloss does not currently exist in the dictionary,
            you can <a href={CommonPaths.contributions.lexicalEntry} target="_blank" rel="noreferrer">publish the gloss as a separate contribution</a>{' '}
            <em>(opens in a new tab)</em>.
        </p>
        <div className="form-group">
            <label htmlFor="ed-sentence-text-body">Text body</label>
            <textarea id="ed-sentence-text-body"
                      className="form-control"
                      onChange={_onChangeNative}
                      rows={10}
                      value={text}
                      placeholder={`She walks in beauty, like the night
Of cloudless climes and starry skies;
And all that’s best of dark and bright
Meet in her aspect and her eyes;
Thus mellowed to that tender light
Which heaven to gaudy day denies.`}
            />
            <div className="text-center mt-3">
                <button className="btn btn-primary btn-block"
                        disabled={! textIsDirty}
                        onClick={_onParseFragments}>Update</button>
            </div>
        </div>
        <hr />
        {fragments.length > 0 ? <FragmentsGrid
            fragments={fragments}
            languageId={languageId}
            onChange={onFragmentChange}
        /> : <p className="text-center">
            Enter a phrase and press the button above to get started.
        </p>}
    </>;
}

export default FragmentsForm;
