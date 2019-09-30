import React, { useCallback, useState } from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import GlossSelect from '@root/components/Form/GlossSelect';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Quote from '@root/components/Quote';
import GlossResourceApiConnector from '@root/connectors/backend/GlossResourceApiConnector';
import ValidationError from '@root/connectors/ValidationError';
import SharedReference from '@root/utilities/SharedReference';
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
        const api = SharedReference.getInstance(GlossResourceApiConnector);
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
            Do you want to delete the gloss <Quote>{gloss.word}</Quote>?
            <GlossSelect name="test" onChange={(e) => setReplacementId(e.value)} value={replacementId} />
        </Dialog>
        <a href="#" onClick={_onOpen}>
            <span className="glyphicon glyphicon-trash" />
        </a>
    </>;
}

export default DeleteGloss;
