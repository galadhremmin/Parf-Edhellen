import {
    afterEach,
    beforeAll,
    describe,
    expect,
    test
} from '@jest/globals';
import { render, screen, waitFor } from '@testing-library/react';
import sinon, { type SinonSandbox, type SinonStubbedInstance } from 'sinon';

import type IUtilityApi from '@root/connectors/backend/IUtilityApi';
import UtilityApiConnector from '@root/connectors/backend/UtilityApiConnector';
import Markdown from './Markdown';

describe('components/Markdown', () => {
    const MarkdownText = 'This *text* is **bold**!';
    const HtmlText = 'This <i>text</i> is <b>bold</b>!';

    let sandbox: SinonSandbox;
    let markdownApiStub: SinonStubbedInstance<IUtilityApi>;

    beforeAll(() => {
        sandbox = sinon.createSandbox();

        markdownApiStub = sinon.createStubInstance(UtilityApiConnector);
        markdownApiStub.parseMarkdown.resolves({
            html: HtmlText,
        });
    })

    afterEach(() => {
        sandbox.restore();
    });

    test('mounts', async () => {
        render(<Markdown parse={false} text={MarkdownText} markdownApi={markdownApiStub} />);

        await waitFor(() => {
            const markdownText = screen.getByText(MarkdownText);
            expect(markdownText).toEqual(expect.anything());
        });
    });

    test('makes the right request', async () => {

        render(<Markdown parse={true} text={MarkdownText} markdownApi={markdownApiStub} />);

        await waitFor(() => {
            expect(markdownApiStub.parseMarkdown.calledOnceWith({
                markdown: MarkdownText,
            })).toBeTruthy();
        });
    });

    test('can parse', async () => {
        render(<Markdown parse={true} text={MarkdownText} markdownApi={markdownApiStub} />);
        
        await waitFor(() => {
            const textContent = screen.getAllByText('text');
            const boldContent = screen.getAllByText('bold');

            expect(textContent.length).toEqual(1);
            expect(boldContent.length).toEqual(1);
        });
    });
});
