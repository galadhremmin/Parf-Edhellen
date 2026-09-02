import { useCallback, useState } from 'react';
import type { FocusEvent } from 'react';

import CopiableTextInput from '@root/components/CopiableTextInput';
import Panel from '@root/components/Panel';
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
 * Sharing controls for a word list: whether it is readable by others, and the addresses to hand out.
 *
 * Presented as a panel on the page rather than behind a link, because the public/private state is
 * something the owner should be able to see without asking for it — a list they believe is private
 * and a list that is readable by anybody look otherwise identical.
 *
 * Visibility is a plain flag, not a secret link: a public list is readable by everybody, whether or
 * not they were given the address. The summary says so before the owner makes the list public,
 * because "share this with a friend" and "publish this to everyone" are the same action here.
 */
function ShareWordList(props: IProps) {
    const { canEdit, onVisibilityChange, wordList } = props;

    const [ isCopied, setIsCopied ] = useState<boolean>(false);
    const [ saving, setSaving ] = useState<boolean>(false);
    const [ failed, setFailed ] = useState<boolean>(false);

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

    return <Panel className="ShareWordList"
                  title={<><TextIcon icon="share" /> Sharing</>}
                  titleButton={<span className={`badge rounded-pill ${isPublic ? 'bg-success' : 'bg-secondary'}`}>
                      <TextIcon icon={isPublic ? 'globe' : 'lock'} />
                      {' '}
                      {isPublic ? 'Public' : 'Private'}
                  </span>}>

        {failed && <StaticAlert type="danger">
            We could not change the visibility of this list. Please try again.
        </StaticAlert>}

        {isCopied && <StaticAlert type="success">
            <TextIcon icon="info-sign" />{' '}
            Copied the text! It is now ready to be pasted elsewhere.
        </StaticAlert>}

        <p className="ShareWordList--summary">
            {isPublic
                ? <>Anyone can read this list, including people who were not given the address. It
                    does not show anything about you beyond your nickname.</>
                : <>Only you can open the addresses below. Make the list public to let anyone read
                    it.</>}
        </p>

        <div className="ShareWordList--links">
            <div className="ShareWordList--link">
                <label htmlFor={`ed-share-word-list-url-${wordList.id}`} className="form-label">
                    Direct link
                </label>
                <CopiableTextInput onCopyActionSuccess={_onCopy}
                                   onCopyActionFail={_onCopyFail}
                                   type="text"
                                   className="form-control"
                                   id={`ed-share-word-list-url-${wordList.id}`}
                                   value={url}
                                   readOnly
                                   onFocus={onInputFocus} />
            </div>

            <div className="ShareWordList--link">
                <label htmlFor={`ed-share-word-list-markdown-${wordList.id}`} className="form-label">
                    Discuss (markdown link)
                </label>
                <CopiableTextInput onCopyActionSuccess={_onCopy}
                                   onCopyActionFail={_onCopyFail}
                                   type="text"
                                   className="form-control"
                                   id={`ed-share-word-list-markdown-${wordList.id}`}
                                   value={markdown}
                                   readOnly
                                   onFocus={onInputFocus} />
            </div>
        </div>

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
            <span className="ShareWordList--visibility-hint text-muted">
                {isPublic
                    ? 'Making it private again hides it from everybody but you.'
                    : 'You can make it private again at any time.'}
            </span>
        </div>}
    </Panel>;
}

export default ShareWordList;
