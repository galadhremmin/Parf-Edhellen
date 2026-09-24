import { describe, expect, jest, test } from '@jest/globals';
import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { useState } from 'react';

import type { IComponentEvent } from '@root/components/Component._types';
import type ISenseApi from '@root/connectors/backend/ISenseApi';
import type { ISenseSuggestionsResponse } from '@root/connectors/backend/ISenseApi';

import SenseSelect from './SenseSelect';
import type { ISenseSelection } from './SenseSelect._types';

const cottage = {
    definition: 'a small house with a single story',
    entries: 1,
    id: 812,
    label: 'bungalow',
    lineage: [ 'house', 'building' ],
    synonyms: [ 'cottage' ],
};

const senseInUse = {
    entries: 7,
    sense: 'cottage, house',
    senseId: 42650,
};

type SenseChange = (ev: IComponentEvent<ISenseSelection>) => void;

const changeSpy = () => jest.fn<SenseChange>();

function senseApi(response: Partial<ISenseSuggestionsResponse> = {}): ISenseApi {
    return {
        find: jest.fn(async () => ({
            concepts: response.concepts ?? [ cottage ],
            senses: response.senses ?? [ senseInUse ],
        })),
    } as unknown as ISenseApi;
}

const field = () => screen.getByRole('textbox');

/**
 * The picker as a form holds it: what it reports back becomes what it shows.
 */
function Harness(props: { onChange?: SenseChange } & Partial<ISenseSelection> & { senseApi: ISenseApi }) {
    const { onChange, senseApi, ...initial } = props;
    const [ selection, setSelection ] = useState<ISenseSelection>({ sense: '', ...initial });

    return <SenseSelect
        {...selection}
        name="sense"
        onChange={(ev) => {
            setSelection(ev.value);
            onChange?.(ev);
        }}
        senseApi={senseApi}
    />;
}

/**
 * Types into the one field there is, and waits for what it offers.
 */
async function suggest(text: string, heading: RegExp) {
    fireEvent.change(field(), { target: { value: text } });
    await waitFor(() => expect(screen.getByText(heading)).toBeTruthy());
}

describe('components/Form/SenseSelect', () => {
    test('keeps the sense a free wording', () => {
        const onChange = changeSpy();
        render(<SenseSelect name="sense" onChange={onChange} sense="cottage, house" senseApi={senseApi()} />);

        fireEvent.change(field(), { target: { value: 'cottage, hut' } });

        expect(onChange.mock.calls[0][0].value.sense).toBe('cottage, hut');
    });

    test('offers the wordings already in use, and the meanings behind them', async () => {
        render(<Harness senseApi={senseApi()} />);

        await suggest('cotta', /Wordings already in use/);

        expect(screen.getByText('cottage, house')).toBeTruthy();
        expect(screen.getByText('7 entries')).toBeTruthy();
        expect(screen.getByText('bungalow')).toBeTruthy();
        expect(screen.getByText(/also cottage/)).toBeTruthy();
        expect(screen.getByText(/a kind of house ‹ building/)).toBeTruthy();
    });

    test('choosing a meaning keeps the wording and reports the concept', async () => {
        const onChange = changeSpy();
        render(<Harness onChange={onChange} senseApi={senseApi()} />);

        await suggest('cottage, house', /Meanings/);
        onChange.mockClear();
        fireEvent.click(screen.getByText('bungalow'));

        const selection = onChange.mock.calls[0][0].value;
        expect(selection.conceptId).toBe(812);
        expect(selection.sense).toBe('cottage, house');
    });

    test('the wordings offered once a meaning is chosen are the ones that mean it', async () => {
        const api = senseApi();
        render(<Harness concept={cottage} conceptId={812} senseApi={api} />);

        await suggest('cot', /Wordings that already mean this/);

        expect(api.find).toHaveBeenCalledWith('cot', 812);
        // the meaning is settled, so it is not offered again
        expect(screen.queryByText('Meanings')).toBeNull();
    });

    test('choosing a wording in use joins that very sense', async () => {
        const onChange = changeSpy();
        render(<Harness onChange={onChange} senseApi={senseApi()} />);

        await suggest('cotta', /Wordings already in use/);
        onChange.mockClear();
        fireEvent.click(screen.getByText('cottage, house'));

        const selection = onChange.mock.calls[0][0].value;
        expect(selection.sense).toBe('cottage, house');
        expect(selection.senseId).toBe(42650);
    });

    test('rewording it is no longer the sense that was picked', () => {
        const onChange = changeSpy();
        render(<SenseSelect
            conceptId={812}
            name="sense"
            onChange={onChange}
            sense="cottage, house"
            senseApi={senseApi()}
            senseId={42650}
        />);

        fireEvent.change(field(), { target: { value: 'cottage, house!' } });

        const selection = onChange.mock.calls[0][0].value;
        expect(selection.senseId).toBeUndefined();
        // the wording changed; what it means need not have
        expect(selection.conceptId).toBe(812);
    });

    test('a chosen meaning is shown and can be taken away', () => {
        const onChange = changeSpy();
        render(<SenseSelect
            concept={cottage}
            conceptId={812}
            name="sense"
            onChange={onChange}
            sense="cottage, house"
            senseApi={senseApi()}
        />);

        expect(screen.getByText('a small house with a single story')).toBeTruthy();
        fireEvent.click(screen.getByRole('button', { name: 'Remove' }));

        const selection = onChange.mock.calls[0][0].value;
        expect(selection.conceptId).toBeUndefined();
        expect(selection.sense).toBe('cottage, house');
    });
});
