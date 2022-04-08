import IBookApi, { IBookGlossEntity } from '@root/connectors/backend/IBookApi';
import { DI, resolve } from '@root/di';
import { useEffect, useState } from 'react';

import { IHookedGloss } from './useGloss._types';

function useGloss<T extends IBookGlossEntity = IBookGlossEntity>(glossId: number, adapter?: (gloss: IBookGlossEntity) => T): IHookedGloss<T> {
    const [ gloss, setGloss ] = useState<IHookedGloss<T>>(null);

    useEffect(() => {
        if (! glossId) {
            setGloss(null);
        } else {
            const api = resolve<IBookApi>(DI.BookApi);
            api.gloss(glossId) //
                .then((details) => {
                    const entity = details.sections[0].entities[0];
                    let nextGloss;
                    if (typeof adapter === 'function') {
                        nextGloss = adapter(entity);
                    } else {
                        nextGloss = entity as T;
                    }

                    setGloss({
                        error: null,
                        gloss: nextGloss,
                    });
                }) //
                .catch((ex) => {
                    const error = `[useGloss]: Failed to resolve gloss ${glossId}: ${ex}`;
                    console.warn(error);
                    setGloss({
                        error,
                        gloss: null,
                    });
                });
        }
    }, [ glossId ]);

    return gloss;
}

export default useGloss;
