import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';
import sinon from 'sinon';

import Tengwar from './Tengwar';
import { IProps, ITranscriber } from './Tengwar._types';

import '../utilities/Enzyme';

describe('components/Tengwar', () => {
    let wrapper: ReactWrapper<IProps>;
    const DefaultMessage = 'Hello world!';
    const DefaultMode = 'sindarin';
    const DefaultModeName = 'unit-test';

    before(() => {
        wrapper = mount(<Tengwar text={DefaultMessage} />);
    });

    it('does mount', () => {
        expect(wrapper.find('span')).to.exist;
        expect(wrapper.text()).to.equal(DefaultMessage);
    });

    it('can transcribe with Glaemscribe', (done) => {
        const text = 'ai na vadui dúnadan! mae govannen!';
        const expected = 'yeah...';

        wrapper.setProps({
            mode: DefaultMode,
            text,
            transcribe: true,
            // tslint:disable-next-line: new-parens
            transcriber: new class MockedTranscriber implements ITranscriber {
                public transcribe = sinon.stub()
                    .withArgs(text, DefaultMode)
                    .returns(expected);
                public getModeName = () => Promise.resolve(DefaultModeName);
            },
        });

        // Let the promises evaluate asynchronously as the transcriber is written
        // asynchronously as it is expected to load static resources.
        setTimeout(() => {
            wrapper.update();
            expect(wrapper.text()).to.equal(expected);
            expect(wrapper.getDOMNode().getAttribute('title')).to.equal(`${text} (${DefaultModeName})`);
            done();
        }, 0);
    });
});
