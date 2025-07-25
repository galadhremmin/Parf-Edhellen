import React, { useCallback, useState } from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import GlossSelect from '@root/components/Form/GlossSelect';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Quote from '@root/components/Quote';
import TextIcon from '@root/components/TextIcon';
import ValidationError from '@root/connectors/ValidationError';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { IProps } from './index._types';

function DeleteLexicalEntry(props: IProps) {
    const [ isOpen, setIsOpen ] = useState(false);
    const [ errors, setErrors ] = useState<ValidationError>(null);
    const [ replacementId, setReplacementId ] = useState(0);

    const {
        lexicalEntry,
    } = props;

    const lexicalEntryId = lexicalEntry.id;
    const title = <>
        Delete <Quote>{lexicalEntry.word}</Quote> ({lexicalEntry.id})
    </>;

    const _onOpen = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onDelete = useCallback(async (ev: IComponentEvent<number>) => {
        const api = resolve(DI.GlossApi);
        try {
            await api.delete(lexicalEntryId, ev.value);
            setIsOpen(false);
            setErrors(null);
        } catch (e) {
            setErrors(e as ValidationError);
        }
    }, [ setIsOpen, lexicalEntryId, setErrors ]);

    return <>
        <Dialog<number> open={isOpen}
                        onDismiss={_onDismiss}
                        onConfirm={_onDelete}
                        title={title}
                        value={replacementId}>
            <ValidationErrorAlert error={errors} />
            <strong>
                Do you want to delete the lexical entry <Quote>{lexicalEntry.word}</Quote>?
            </strong>
            <p>
                It is recommended to provide an alternative entry to ensure that there are no dangling
                references as a result of the deletion, such as phrases depending on the entry:
            </p>
            <GlossSelect name="test" onChange={(e) => setReplacementId(e.value)} value={replacementId} />
            <p>
                Remember! An entry can't be <em>permanently</em>{' '}deleted. A deleted entry can be restored.
            </p>
        </Dialog>
        <a href="#" onClick={_onOpen}>
            <TextIcon icon="trash" />
        </a>
    </>;
}

export default DeleteLexicalEntry;
