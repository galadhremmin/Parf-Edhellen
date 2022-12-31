import { render, screen } from '@testing-library/react';
import axios from 'axios';
import { expect } from 'chai';
import React from 'react';
import sinon, { SinonSandbox } from 'sinon';

import Markdown from './Markdown';

describe('components/Markdown', () => {
    const MarkdownText = 'This *text* is **bold**!';
    const HtmlText = 'This <i>text</i> is <b>bold</b>!';

    let sandbox: SinonSandbox;

    before(() => {
        sandbox = sinon.createSandbox();
    });

    afterEach(() => {
        sandbox.restore();
    });

    it('mounts', async () => {
        render(<Markdown parse={false} text={MarkdownText} />);
        const markdownText = await screen.findByText(MarkdownText);
        expect(markdownText).to.exist;
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

        render(<Markdown parse={true} text={MarkdownText} />);
    });

    it('can parse', async () => {
        const markdownResponse = Promise.resolve({
            data: {
                html: HtmlText,
            },
            status: 200,
        });

        sandbox.stub(axios, 'post')
            .callsFake(() => markdownResponse);

        render(<Markdown parse={true} text={MarkdownText} />);
        
        const textContent = await screen.findAllByText('text');
        const boldContent = await screen.findAllByText('bold');

        expect(textContent.length).to.equal(1);
        expect(boldContent.length).to.equal(1);
    });
});
