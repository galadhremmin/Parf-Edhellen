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
        id: lexicalEntryId,
        language,
        word: glossWord,
    } = props.lexicalEntry;

    const [ isShareOpen, setIsShareOpen ] = useState<boolean>(false);
    const [ isCopied, setIsCopied ] = useState<boolean>(false);

    const _onClick = useCallback((ev: React.MouseEvent) => {
        ev.preventDefault();
        setIsShareOpen(true);
    }, [ lexicalEntryId ]);

    const _onDismiss = useCallback(() => {
        setIsShareOpen(false);
    }, []);

    const _onCopy = useCallback(() => {
        setIsCopied(true);
    }, [ lexicalEntryId ]);

    const _onCopyFail = useCallback(() => {
        setIsCopied(false);
    }, [ lexicalEntryId ]);

    const path = `/wt/${lexicalEntryId}`;
    const url = `${window.location.origin}${path}`;
    const markdownLocalLink = `[${glossWord}](${path})`;
    const markdownRemoteLink = `[${glossWord}](${url})`;
    const markdown = `[[${language.shortName}|${glossWord}]]`;

    return <>
        <Dialog<void> open={isShareOpen}
            onDismiss={_onDismiss}
            title={<>Share <Quote>{glossWord}</Quote></>}>
            <p>
                There are several ways you can share a lexical entry and some of them are listed below.
            </p>
            {isCopied && <StaticAlert type="success">
                <TextIcon icon="info-sign" />{' '}
                Copied the text! It is now ready to be pasted elsewhere.
            </StaticAlert>}
            <label htmlFor={`ed-form-share-address-${lexicalEntryId}`} className="form-label">Direct link</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-address-${lexicalEntryId}`}
                value={url}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-markdown-${lexicalEntryId}`} className="form-label">Discuss (direct link)</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-markdown-${lexicalEntryId}`}
                value={markdownLocalLink}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-language-word-markdown-${lexicalEntryId}`} className="form-label">Discuss (can yield multiple definitions of the same word)</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-language-word-markdown-${lexicalEntryId}`}
                value={markdown}
                readOnly
                onFocus={onInputFocus}
            />

            <label htmlFor={`ed-form-share-markdown-remote-${lexicalEntryId}`} className="form-label">Markdown direct link</label>
            <CopiableTextInput formGroupClassName="mb-3"
                onCopyActionSuccess={_onCopy}
                onCopyActionFail={_onCopyFail}
                type="text"
                className="form-control"
                id={`ed-form-share-markdown-remote-${lexicalEntryId}`}
                value={markdownRemoteLink}
                readOnly
                onFocus={onInputFocus}
            />
        </Dialog>
        <a href={url} onClick={_onClick} title={`Share "${glossWord}" lexical entry`}>
            <TextIcon icon="share" />
        </a>
    </>;
}

export default ShareLink;
