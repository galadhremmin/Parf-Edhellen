import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useState } from 'react';

import { IGlossHookOptions, IHookedGloss } from './useGloss._types';

const NoGloss: IHookedGloss<any> = {
    error: null,
    gloss: null,
};

function useGloss<T extends IBookGlossEntity = IBookGlossEntity>(glossId: number, options: IGlossHookOptions<T> = {}): IHookedGloss<T> {
    const {
        isEnabled,
    } = options;

    const [ gloss, setGloss ] = useState<IHookedGloss<T>>(NoGloss);

    useEffect(() => {
        const disabled = isEnabled !== undefined && ! isEnabled;

        if (! glossId) {
            setGloss(NoGloss);
        } else if (! disabled) {
            const api = resolve(DI.BookApi);
            api.gloss(glossId) //
                .then((details) => {
                    const entity = details.sections[0].entities[0];
                    let nextGloss;
                    if (typeof options?.glossAdapter === 'function') {
                        nextGloss = options.glossAdapter(entity);
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
    }, [ glossId, isEnabled ]);

    return gloss;
}

export default useGloss;
