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
import { render, screen } from '@testing-library/react';

describe('components/Form', () => {
    describe('MarkdownInput', () => {
        const MarkdownText = 'This *text* is **bold**!';
        const HtmlText = 'This <em>text</em> is <b>bold</b>!';

        let sandbox: SinonSandbox;
        let config: () => Cache<IComponentConfig>;

        before(() => {
            sandbox = sinon.createSandbox();
        });

        beforeEach(() => {
            sandbox.stub(axios, 'post').returns(Promise.resolve(HtmlText));

            // This is necessary as `localstorage` is not supported by Mocha (in this context).
            config = () => new Cache<IComponentConfig>(() => Promise.resolve({
                enter2Paragraph: true,
            }), new MemoryStorage(), 'unit-test');
        });

        afterEach(() => {
            sandbox.restore();
        });

        it('mounts', async () => {
            render(<MarkdownInput value={MarkdownText} configCacheFactory={config} />);

            const textareas = await screen.findAllByRole('textbox');
            expect(textareas).to.have.lengthOf(1);
            expect(textareas[0]).to.have.property('value', MarkdownText);
        });

        it('respects required', async () => {
            render(<MarkdownInput value={MarkdownText} configCacheFactory={config} required={true} />);

            expect(await screen.findByRole('textbox')).to.have.property('required', true);
        });
    });
});
