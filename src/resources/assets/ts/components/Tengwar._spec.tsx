import {
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import { render, screen } from '@testing-library/react';
import sinon from 'sinon';

import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';
import Tengwar from './Tengwar';
import { ITranscriber } from './Tengwar._types';

describe('components/Tengwar', () => {
    const DefaultMode = 'sindarin';
    const DefaultModeName = 'unit-test';

    beforeAll(() => {
        setInstance(DI.GlobalEvents, GlobalEventConnector);
    });

    test('can transcribe with Glaemscribe', async () => {
        const text = 'ai na vadui dúnadan! mae govannen!';
        const expected = 'yeah...';
        const transcriber = new class MockedTranscriber implements ITranscriber {
            public transcribe = sinon.stub()
                .withArgs(text, DefaultMode)
                .returns(expected);
            public getModeName = () => Promise.resolve(DefaultModeName);
        };

        render(<Tengwar mode={DefaultMode} text={text} transcribe={true} transcriber={transcriber} />);

        const tengwar = await screen.findByText(expected);
        expect(tengwar).toEqual(expect.anything());
        const title = await screen.findByTitle(`${text} (${DefaultModeName})`);
        expect(title).toEqual(expect.anything());
    });
});
