import axios from 'axios';
import { expect } from 'chai';
import React from 'react';
import sinon, { SinonSandbox } from 'sinon';

import Markdown from './Markdown';

/*
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
                return Promise.resolve({
                    html: ''
                });
            });

        wrapper.setProps({
            parse: true,
        });
    });

    it('can parse', () => {
        const markdownResponse = Promise.resolve({
            data: {
                html: HtmlText,
            },
            status: 200,
        });

        sandbox.stub(axios, 'post')
            .callsFake(() => markdownResponse);

        wrapper.setProps({
            parse: true,
        });

        markdownResponse.then(() => {
            expect(wrapper.children().length).to.equal(1);

            let c = wrapper.childAt(0).find('i');
            expect(c.length).to.equal(1);
            expect(c.first().text()).to.equal('text');

            c = wrapper.childAt(0).find('b');
            expect(c.length).to.equal(1);
            expect(c.first().text()).to.equal('bold');
        });
    });
});
*/
