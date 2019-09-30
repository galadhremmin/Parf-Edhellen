import React, {
    useCallback,
    useState,
} from 'react';

import GlossForm from '@root/apps/form-gloss/containers/GlossForm';
import Dialog from '@root/components/Dialog';
import Quote from '@root/components/Quote';
import { IProps } from './index._types';

function EditGloss(props: IProps) {
    const [ isOpen, setIsOpen ] = useState(false);

    const _onOpen = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const {
        gloss,
    } = props;

    return <>
        <Dialog<boolean> open={isOpen} onDismiss={_onDismiss} title={`Edit ${gloss.word} (${gloss.id})`}>
            Edit gloss.
        </Dialog>
        <a href={`/dashboard/contribution/create/gloss?entity_id=${gloss.id}`} onClick={_onOpen}>
            <span className="glyphicon glyphicon-pencil" />
        </a>
    </>;
}

export default EditGloss;
