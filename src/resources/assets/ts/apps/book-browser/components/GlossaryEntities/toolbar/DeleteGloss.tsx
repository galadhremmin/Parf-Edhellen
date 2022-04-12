import React, { useCallback, useState } from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import GlossSelect from '@root/components/Form/GlossSelect';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Quote from '@root/components/Quote';
import TextIcon from '@root/components/TextIcon';
import IGlossResourceApi from '@root/connectors/backend/IGlossResourceApi';
import ValidationError from '@root/connectors/ValidationError';
import { DI, resolve } from '@root/di';
import { IProps } from './index._types';

function DeleteGloss(props: IProps) {
    const [ isOpen, setIsOpen ] = useState(false);
    const [ errors, setErrors ] = useState<ValidationError>(null);
    const [ replacementId, setReplacementId ] = useState(0);

    const {
        gloss,
    } = props;

    const glossId = gloss.id;
    const title = <>
        Delete <Quote>{gloss.word}</Quote> ({gloss.id})
    </>;

    const _onOpen = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onDelete = useCallback(async (ev: IComponentEvent<number>) => {
        const api = resolve<IGlossResourceApi>(DI.GlossApi);
        try {
            await api.delete(glossId, ev.value);
            setIsOpen(false);
            setErrors(null);
        } catch (e) {
            setErrors(e as ValidationError);
        }
    }, [ setIsOpen, glossId, setErrors ]);

    return <>
        <Dialog<number> open={isOpen}
                        onDismiss={_onDismiss}
                        onConfirm={_onDelete}
                        title={title}
                        value={replacementId}>
            <ValidationErrorAlert error={errors} />
            <strong>
                Do you want to delete the gloss <Quote>{gloss.word}</Quote>?
            </strong>
            <p>
                It is recommended to provide an alternative gloss to ensure that there are no dangling
                references as a result of the deletion, such as phrases depending on the gloss:
            </p>
            <GlossSelect name="test" onChange={(e) => setReplacementId(e.value)} value={replacementId} />
            <p>
                Remember! A gloss can't be <em>permanently</em>{' '}deleted. A deleted gloss can be restored.
            </p>
        </Dialog>
        <a href="#" onClick={_onOpen}>
            <TextIcon icon="trash" />
        </a>
    </>;
}

export default DeleteGloss;
