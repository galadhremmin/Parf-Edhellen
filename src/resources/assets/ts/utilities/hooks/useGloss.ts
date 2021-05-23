import IBookApi, { IBookGlossEntity } from '@root/connectors/backend/IBookApi';
import { DI, resolve } from '@root/di';
import { useEffect, useState } from 'react';

function useGloss<T extends IBookGlossEntity = IBookGlossEntity>(glossId: number, adapter?: (gloss: IBookGlossEntity) => T) {
    const [ gloss, setGloss ] = useState<T>(null);

    useEffect(() => {
        if (! glossId) {
            setGloss(null);
        } else {
            const api = resolve<IBookApi>(DI.BookApi);
            api.gloss(glossId) //
                .then((details) => {
                    const entity = details.sections[0].entities[0];
                    if (typeof adapter === 'function') {
                        setGloss(adapter(entity));
                    } else {
                        setGloss(entity as T);
                    }
                }) //
                .catch((ex) => {
                    console.warn(`[useGloss]: Failed to resolve gloss ${glossId}: ${ex}`);
                    setGloss(null);
                });
        }
    }, [ glossId ]);

    return gloss;
}

export default useGloss;
