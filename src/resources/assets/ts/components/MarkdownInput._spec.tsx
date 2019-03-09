import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';
import sinon, { SinonSandbox } from 'sinon';

import Cache from '@root/utilities/Cache';
import MemoryStorage from '@root/utilities/MemoryStorage';

import MarkdownInput from './MarkdownInput';
import { IComponentConfig } from './MarkdownInput._types';

import '../utilities/Enzyme';

describe('components/MarkdownInput', () => {
    const MarkdownText = 'This *text* is **bold**!';

    let wrapper: ReactWrapper;
    let sandbox: SinonSandbox;

    before(() => {
        // This is necessary as `localstorage` is not supported by Mocha (in this context).
        const config = () => new Cache<IComponentConfig>(() => Promise.resolve({
            enter2Paragraph: true,
        }), new MemoryStorage(), 'unit-test');

        wrapper = mount(<MarkdownInput value={MarkdownText} configCacheFactory={config} />);
        sandbox = sinon.createSandbox();
    });

    beforeEach(() => {
        // TODO: Override axios' POST request.
    });

    afterEach(() => {
        sandbox.restore();
    });

    it('mounts', () => {
        const textareas = wrapper.find('textarea');
        expect(textareas).to.have.lengthOf(1);
        expect(textareas.getDOMNode()).to.have.property('value', MarkdownText);
    });

    it('mounts', () => {
        wrapper.setProps({
            required: true,
        });
        expect(wrapper.find('textarea').getDOMNode()).to.have.property('required', true);
    });
});
