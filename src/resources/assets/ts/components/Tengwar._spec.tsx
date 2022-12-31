import { render, screen } from '@testing-library/react';
import { expect } from 'chai';
import React from 'react';
import sinon from 'sinon';

import Tengwar from './Tengwar';
import { ITranscriber } from './Tengwar._types';

describe('components/Tengwar', () => {
    const DefaultMode = 'sindarin';
    const DefaultModeName = 'unit-test';

    it('can transcribe with Glaemscribe', async () => {
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
        expect(tengwar).to.exist;
        const title = await screen.findByTitle(`${text} (${DefaultModeName})`);
        expect(title).to.exist;
    });
});
