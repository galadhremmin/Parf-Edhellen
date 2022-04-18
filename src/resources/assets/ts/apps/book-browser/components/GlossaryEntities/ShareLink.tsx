import React, { useCallback, useState } from 'react';

import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import Quote from '@root/components/Quote';
import CopiableTextInput from '@root/components/CopiableTextInput';
import StaticAlert from '@root/components/StaticAlert';
import { IProps } from './ShareLink._types';

const onInputFocus = (ev: React.FocusEvent<HTMLInputElement>) => {
    ev.target.select();
}

function ShareLink(props: IProps) {
    const {
        id: glossId,
        language,
        word: glossWord,
    } = props.gloss;

    const [ isShareOpen, setIsShareOpen ] = useState<boolean>(false);
    const [ isCopied, setIsCopied ] = useState<boolean>(false);

    const _onClick = useCallback((ev: React.MouseEvent) => {
        ev.preventDefault();
        setIsShareOpen(true);
    }, [ glossId ]);

    const _onDismiss = useCallback(() => {
        setIsShareOpen(false);
    }, []);

    const _onCopy = useCallback(() => {
        setIsCopied(true);
    }, [ glossId ]);

    const _onCopyFail = useCallback(() => {
        setIsCopied(false);
    }, [ glossId ]);

    const path = `/wt/${glossId}`;
    const url = `${window.location.origin}${path}`;
    const markdownLocalLink = `[${glossWord}](${path})`;
    const markdownRemoteLink = `[${glossWord}](${url})`;
    const markdown = `[[${language.shortName}|${glossWord}]]`;

    return <>
        <Dialog<void> open={isShareOpen}
            onDismiss={_onDismiss}
            title={<>Share <Quote>{glossWord}</Quote></>}>
            <p>
                There are several ways you can share a gloss and some of them are listed below.
            </p>
            {isCopied && <StaticAlert type="success">
                <TextIcon icon="info-sign" />{' '}
                Copied the text! It is now ready to be pasted elsewhere.
            </StaticAlert>}
            <label htmlFor={`ed-form-share-address-${glossId}`} className="form-label">Direct link</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-address-${glossId}`}
                value={url}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-markdown-${glossId}`} className="form-label">Discuss (direct link)</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-markdown-${glossId}`}
                value={markdownLocalLink}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-language-word-markdown-${glossId}`} className="form-label">Discuss (can yield multiple definitions of the same word)</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-language-word-markdown-${glossId}`}
                value={markdown}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-markdown-remote-${glossId}`} className="form-label">Direct link</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-markdown-remote-${glossId}`}
                value={markdownRemoteLink}
                readOnly
                onFocus={onInputFocus}
            />
        </Dialog>
        <a href={url} onClick={_onClick}>
            <TextIcon icon="share" />
        </a>
    </>;
}

export default ShareLink;
