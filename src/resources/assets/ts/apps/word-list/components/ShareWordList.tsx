import { useCallback, useState } from 'react';
import type { FocusEvent, MouseEvent } from 'react';

import CopiableTextInput from '@root/components/CopiableTextInput';
import Dialog from '@root/components/Dialog';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type { IProps } from './ShareWordList._types';

import './ShareWordList.scss';

const onInputFocus = (ev: FocusEvent<HTMLInputElement>) => {
    ev.target.select();
};

/**
 * Offers the address of a word list for sharing.
 *
 * Visibility is a plain public/private flag rather than a secret link: a public list is readable by
 * anybody, whether or not they were given the address. The dialog says so before the owner makes
 * the list public, because "share this with a friend" and "publish this to everyone" are the same
 * action here, and the difference matters to the person deciding.
 */
function ShareWordList(props: IProps) {
    const { canEdit, onVisibilityChange, wordList } = props;

    const [ isOpen, setIsOpen ] = useState<boolean>(false);
    const [ isCopied, setIsCopied ] = useState<boolean>(false);
    const [ saving, setSaving ] = useState<boolean>(false);
    const [ failed, setFailed ] = useState<boolean>(false);

    const _onOpen = useCallback((ev: MouseEvent) => {
        ev.preventDefault();
        setIsCopied(false);
        setFailed(false);
        setIsOpen(true);
    }, []);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, []);

    const _onCopy = useCallback(() => {
        setIsCopied(true);
    }, []);

    const _onCopyFail = useCallback(() => {
        setIsCopied(false);
    }, []);

    const _setVisibility = useCallback(async (isPublic: boolean) => {
        setSaving(true);
        setFailed(false);

        try {
            const api = resolve(DI.WordListApi);
            await api.update(wordList.id, { isPublic });
            onVisibilityChange(isPublic);
        } catch {
            setFailed(true);
        } finally {
            setSaving(false);
        }
    }, [ onVisibilityChange, wordList.id ]);

    const url = wordList.url;
    const markdown = `[${wordList.name}](${url})`;
    const isPublic = wordList.isPublic ?? false;

    return <>
        <Dialog<void> open={isOpen}
                      onDismiss={_onDismiss}
                      actionBar={false}
                      title={<>Share &ldquo;{wordList.name}&rdquo;</>}>

            {failed && <StaticAlert type="danger">
                We could not change the visibility of this list. Please try again.
            </StaticAlert>}

            {isCopied && <StaticAlert type="success">
                <TextIcon icon="info-sign" />{' '}
                Copied the text! It is now ready to be pasted elsewhere.
            </StaticAlert>}

            {! isPublic && <StaticAlert type="warning">
                <strong><TextIcon icon="lock" /> This list is private.</strong>
                <p className="mb-0">
                    Only you can open the address below. Make the list public to let anyone read it.
                </p>
            </StaticAlert>}

            {isPublic && <StaticAlert type="info">
                <strong><TextIcon icon="globe" /> This list is public.</strong>
                <p className="mb-0">
                    Anyone can read it, including people who were not given the address.
                    It does not show anything about you beyond your nickname.
                </p>
            </StaticAlert>}

            <label htmlFor={`ed-share-word-list-url-${wordList.id}`} className="form-label">
                Direct link
            </label>
            <CopiableTextInput formGroupClassName="mb-3"
                               onCopyActionSuccess={_onCopy}
                               onCopyActionFail={_onCopyFail}
                               type="text"
                               className="form-control"
                               id={`ed-share-word-list-url-${wordList.id}`}
                               value={url}
                               readOnly
                               onFocus={onInputFocus} />

            <label htmlFor={`ed-share-word-list-markdown-${wordList.id}`} className="form-label">
                Discuss (markdown link)
            </label>
            <CopiableTextInput formGroupClassName="mb-3"
                               onCopyActionSuccess={_onCopy}
                               onCopyActionFail={_onCopyFail}
                               type="text"
                               className="form-control"
                               id={`ed-share-word-list-markdown-${wordList.id}`}
                               value={markdown}
                               readOnly
                               onFocus={onInputFocus} />

            {canEdit && <div className="ShareWordList--visibility">
                {isPublic
                    ? <button type="button"
                              className="btn btn-secondary"
                              disabled={saving}
                              onClick={() => void _setVisibility(false)}>
                        <TextIcon icon="lock" /> Make private
                      </button>
                    : <button type="button"
                              className="btn btn-primary"
                              disabled={saving}
                              onClick={() => void _setVisibility(true)}>
                        <TextIcon icon="globe" /> Make public
                      </button>}
            </div>}
        </Dialog>

        <a href={url} onClick={_onOpen} title={`Share "${wordList.name}"`} className="WordList--share">
            <TextIcon icon="share" /> Share
        </a>
    </>;
}

export default ShareWordList;
