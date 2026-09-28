import { useState } from 'react';

import SenseSelect from '@root/components/Form/SenseSelect';
import type { IComponentEvent } from '@root/components/Component._types';
import type { ISenseSelection } from '@root/components/Form/SenseSelect/SenseSelect._types';

import type { IRewordProps } from './SenseReviewer._types';

/**
 * Corrects the wording itself. Some senses cannot be placed because they are not meanings at all but a lexicographer's
 * shorthand — "card" over a column of numerals is the abbreviation for "cardinal" — and no meaning will ever fit them.
 * The picker is the one the contribution form uses, so a wording already in the dictionary is joined rather than
 * spelled afresh.
 */
const RewordSense = (props: IRewordProps) => {
    const { disabled, entries, onReword, senseApi, wording: current } = props;

    const [ wording, setWording ] = useState<string>('');

    const chosen = wording.trim();
    const unchanged = chosen.toLocaleLowerCase() === current.trim().toLocaleLowerCase();

    return <section className="SenseReviewer--reword">
        <h3 className="h6">Is the wording itself wrong?</h3>
        <p className="text-muted small">
            A sense that is really an abbreviation, a typo or a label has no meaning to find. Give the wording these
            entries should carry and all {entries} move to it, to be placed like any other sense.
        </p>

        <SenseSelect name="reword" sense={wording} senseApi={senseApi}
            onChange={(ev: IComponentEvent<ISenseSelection>) => setWording(ev.value.sense)} />

        <button type="button" className="btn btn-outline-primary mt-2" disabled={disabled || chosen === '' || unchanged}
            onClick={() => onReword(chosen)}>
            Reword {entries} {entries === 1 ? 'entry' : 'entries'}
        </button>
    </section>;
};

export default RewordSense;
