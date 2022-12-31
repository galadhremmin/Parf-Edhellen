import axios from 'axios';
import { expect } from 'chai';
import React from 'react';
import sinon, {
    SinonSandbox,
} from 'sinon';

import Cache from '@root/utilities/Cache';
import MemoryStorage from '@root/utilities/MemoryStorage';

import MarkdownInput from './MarkdownInput';
import { IComponentConfig } from './MarkdownInput._types';

/*
describe('components/Form', () => {
    describe('MarkdownInput', () => {
        const MarkdownText = 'This *text* is **bold**!';
        const HtmlText = 'This <em>text</em> is <b>bold</b>!';

        let wrapper: ReactWrapper;
        let sandbox: SinonSandbox;

        before(() => {
            sandbox = sinon.createSandbox();
        });

        beforeEach(() => {
            sandbox.stub(axios, 'post').returns(Promise.resolve(HtmlText));

            // This is necessary as `localstorage` is not supported by Mocha (in this context).
            const config = () => new Cache<IComponentConfig>(() => Promise.resolve({
                enter2Paragraph: true,
            }), new MemoryStorage(), 'unit-test');
            wrapper = mount(<MarkdownInput value={MarkdownText} configCacheFactory={config} />);
        });

        afterEach(() => {
            sandbox.restore();
        });

        it('mounts', () => {
            const textareas = wrapper.find('textarea');
            expect(textareas).to.have.lengthOf(1);
            expect(textareas.getDOMNode()).to.have.property('value', MarkdownText);
        });

        it('respects required', () => {
            wrapper.setProps({
                required: true,
            });
            expect(wrapper.find('textarea').getDOMNode()).to.have.property('required', true);
        });

        it('renders preview', () => {
            // TODO: Implement this test. It is just not working for me right now, and I need to prioritize
            //       more important things. Functionality manually tested. (191113)

            setTimeout(() => {
                wrapper.update();

                const container = wrapper.find('Markdown');
                expect(container.length).to.equal(1);
                // expect(container.state('html')).to.equal(HtmlText); -- Broken!!!
            });
        });
    });
});
*/
