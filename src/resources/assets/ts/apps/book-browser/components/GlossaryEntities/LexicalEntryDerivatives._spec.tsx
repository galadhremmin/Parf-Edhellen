import { describe, expect, test } from '@jest/globals';
import { render } from '@testing-library/react';

import type { IDerivativeNode, IDerivativesTree } from '@root/connectors/backend/IBookApi';
import LexicalEntryDerivatives from './LexicalEntryDerivatives';
import { LanguageLookupProvider } from './LanguageLookupContext';

function makeNode(overrides: Partial<IDerivativeNode> = {}): IDerivativeNode {
    return {
        children: [],
        form: 'galadh',
        gloss: null,
        isWord: true,
        languageId: null,
        lexicalEntryId: 1,
        source: null,
        url: '/wt/1',
        word: 'galadh',
        ...overrides,
    };
}

function makeTree(children: IDerivativeNode[], truncated = false): IDerivativesTree {
    return { children, truncated };
}

describe('apps/book-browser/components/GlossaryEntities/LexicalEntryDerivatives', () => {
    test('renders nothing when there are no derivatives', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([])} />);
        expect(container.querySelector('.LexicalEntryDerivatives')).toBeFalsy();
    });

    test('renders nothing when derivatives is undefined', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={undefined} />);
        expect(container.querySelector('.LexicalEntryDerivatives')).toBeFalsy();
    });

    test('renders a top-level node linked to its entry', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({ lexicalEntryId: 42, form: 'galadh', word: 'galadh', url: '/wt/42' }),
        ])} />);

        const steps = container.querySelectorAll('.DerivationStepList--step');
        expect(steps).toHaveLength(1);
        const link = steps[0].querySelector('a[href="/wt/42"]');
        expect(link?.textContent).toBe('galadh');
    });

    test('flattens nested children into sibling steps at increasing depth', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({
                lexicalEntryId: 1, form: 'galadā', word: 'galadā', url: '/wt/1',
                children: [makeNode({
                    lexicalEntryId: 2, form: 'galadh', word: 'galadh', url: '/wt/2',
                    children: [makeNode({ lexicalEntryId: 3, form: 'aldalótë', word: 'aldalótë', url: '/wt/3' })],
                })],
            }),
        ])} />);

        const steps = container.querySelectorAll('.DerivationStepList--step');
        expect(steps).toHaveLength(3);
        expect(steps[0].textContent).toContain('galadā');
        expect(steps[1].textContent).toContain('galadh');
        expect(steps[2].textContent).toContain('aldalótë');

        // No nested <ul> — every step is a direct sibling under the top-level list.
        expect(container.querySelectorAll('.DerivationStepList')).toHaveLength(1);
    });

    test('increases indent with depth via the depth custom property', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({
                lexicalEntryId: 1, children: [makeNode({ lexicalEntryId: 2 })],
            }),
        ])} />);

        const steps = container.querySelectorAll<HTMLElement>('.DerivationStepList--step');
        expect(steps[0].style.getPropertyValue('--ed-derivation-depth')).toBe('0');
        expect(steps[1].style.getPropertyValue('--ed-derivation-depth')).toBe('1');
    });

    test('renders an unresolved intermediate form as plain text with the reconstructed marker', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({ lexicalEntryId: null, url: null, isWord: false, form: 'kirissē', word: null }),
        ])} />);

        const step = container.querySelector('.DerivationStepList--step');
        expect(step?.querySelector('a')).toBeFalsy();
        expect(step?.querySelector('span.reconstructed')?.textContent).toBe('kirissē');
    });

    test('renders the gloss and source citation next to the word', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({ gloss: 'tree', source: 'Let/426; PE17/025' }),
        ])} />);

        expect(container.querySelector('.LexicalEntryDerivatives--gloss')?.textContent).toBe('tree');
        expect(container.querySelector('.LexicalEntryDerivatives--source')?.textContent).toContain('Let/426; PE17/025');
    });

    test('omits gloss and source spans when absent', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([
            makeNode({ gloss: null, source: null }),
        ])} />);

        expect(container.querySelector('.LexicalEntryDerivatives--gloss')).toBeFalsy();
        expect(container.querySelector('.LexicalEntryDerivatives--source')).toBeFalsy();
    });

    test('resolves the language badge from the LanguageLookupProvider', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[{ id: 35, name: 'Old Sindarin', shortName: 'os' }]}>
                <LexicalEntryDerivatives derivatives={makeTree([makeNode({ languageId: 35 })])} />
            </LanguageLookupProvider>,
        );

        expect(container.querySelector('.LexicalEntryDerivatives--language')?.textContent).toBe('OS.');
    });

    test('shows a note when the tree was truncated', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([makeNode()], true)} />);
        expect(container.querySelector('.LexicalEntryDerivatives--truncated-note')).toBeTruthy();
    });

    test('shows no truncation note when the tree is complete', () => {
        const { container } = render(<LexicalEntryDerivatives derivatives={makeTree([makeNode()], false)} />);
        expect(container.querySelector('.LexicalEntryDerivatives--truncated-note')).toBeFalsy();
    });
});
