import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';
import sinon from 'sinon';

import SharedReference from '../utilities/SharedReference';
import Tengwar from './Tengwar';
import { IProps } from './Tengwar._types';

import '../utilities/Enzyme';

describe('components/Tengwar', () => {
    let wrapper: ReactWrapper<IProps>;
    const DefaultMessage = 'Hello world!';
    const DefaultMode = 'sindarin';

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
            transcriber: new SharedReference(class MockedTranscriber {
                public transcribe = sinon.stub()
                    .withArgs(text, DefaultMode)
                    .returns(expected);
            }),
        });

        // Let the promises evaluate asynchronously as the transcriber is written
        // asynchronously as it is expected to load static resources.
        setTimeout(() => {
            expect(wrapper.text()).to.equal(expected);
            done();
        }, 1);
    });
});
