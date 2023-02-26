import axios from 'axios';
import {
    afterEach,
    beforeAll,
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';
import sinon, {
    SinonSandbox,
    SinonStubbedInstance,
} from 'sinon';
import { render, screen, waitFor } from '@testing-library/react';

import UtilityApiConnector from '@root/connectors/backend/UtilityApiConnector';
import IUtilityApi from '@root/connectors/backend/IUtilityApi';
import Cache from '@root/utilities/Cache';
import MemoryStorage from '@root/utilities/MemoryStorage';

import MarkdownInput from './MarkdownInput';
import { IComponentConfig } from './MarkdownInput._types';

describe('components/Form', () => {
    describe('MarkdownInput', () => {
        const MarkdownText = 'This *text* is **bold**!';
        const HtmlText = 'This <em>text</em> is <b>bold</b>!';

        let sandbox: SinonSandbox;
        let markdownApiStub: SinonStubbedInstance<IUtilityApi>;
        let config: () => Cache<IComponentConfig>;

        beforeAll(() => {
            sandbox = sinon.createSandbox();

            markdownApiStub = sinon.createStubInstance(UtilityApiConnector);
            markdownApiStub.parseMarkdown.resolves({
                html: HtmlText,
            });
        });

        beforeEach(() => {
            // This is necessary as `localstorage` is not supported by Mocha (in this context).
            config = () => new Cache<IComponentConfig>(() => Promise.resolve({
                enter2Paragraph: true,
            }), new MemoryStorage(), 'unit-test');
        });

        afterEach(() => {
            sandbox.restore();
        });

        test('mounts', async () => {
            render(<MarkdownInput value={MarkdownText} configCacheFactory={config} markdownApi={markdownApiStub} />);

            await waitFor(() => {
                const textareas = screen.getAllByRole('textbox');
                expect(textareas).toHaveLength(1);
                expect(textareas[0]).toHaveProperty('value', MarkdownText);
            });
        });

        test('respects required', async () => {
            render(<MarkdownInput value={MarkdownText} configCacheFactory={config} required={true} markdownApi={markdownApiStub} />);

            await waitFor(() => {
                const textBox = screen.getByRole('textbox');
                expect(textBox).toHaveProperty('required', true);
            });
        });
    });
});
