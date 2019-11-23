import axios from 'axios';
import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';
import sinon, { SinonSandbox } from 'sinon';

import Markdown from './Markdown';

import '../utilities/Enzyme';

describe('components/Markdown', () => {
    const MarkdownText = 'This *text* is **bold**!';
    const HtmlText = 'This <i>text</i> is <b>bold</b>!';

    let wrapper: ReactWrapper;
    let sandbox: SinonSandbox;

    before(() => {
        wrapper = mount(<Markdown parse={false} text={MarkdownText} />);
        sandbox = sinon.createSandbox();
    });

    afterEach(() => {
        sandbox.restore();
    });

    it('mounts', () => {
        expect(wrapper.text()).to.equal(MarkdownText);
    });

    it('makes the right request', (done) => {
        sandbox.stub(axios, 'post')
            .callsFake((path, data) => {
                expect(path).to.contain('utility/markdown');
                expect(data).to.deep.equal({
                    markdown: MarkdownText,
                });

                done();
                return Promise.reject();
            });

        wrapper.setProps({
            parse: true,
        });
    });

    it('can parse', (done) => {
        sandbox.stub(axios, 'post')
            .callsFake(() => {
                return Promise.resolve({
                    data: {
                        html: HtmlText,
                    },
                    status: 200,
                }) as any;
            });

        // force refresh by resetting `lastText`.
        wrapper.setState({
            lastText: null,
        });
        wrapper.setProps({
            parse: true,
        });

        window.setTimeout(() => {
            wrapper.update();

            expect(wrapper.children().length).to.equal(1);

            let c = wrapper.childAt(0).find('i');
            expect(c.length).to.equal(1);
            expect(c.first().text()).to.equal('text');

            c = wrapper.childAt(0).find('b');
            expect(c.length).to.equal(1);
            expect(c.first().text()).to.equal('bold');

            done();
        }, 0);
    });
});
