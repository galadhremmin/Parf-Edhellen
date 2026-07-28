import { describe, expect, test } from '@jest/globals';
import { render } from '@testing-library/react';

import type { IDerivationEntity } from '@root/connectors/backend/IBookApi';
import LexicalEntryDerivations from './LexicalEntryDerivations';
import { LanguageLookupProvider } from './LanguageLookupContext';

function makeStep(overrides: Partial<IDerivationEntity> = {}): IDerivationEntity {
    return {
        comment: null,
        groupUuid: 'group-1',
        intermediateStages: null,
        isRejected: false,
        isUncertain: false,
        order: 0,
        parentForm: 'KIRIS',
        parentGloss: null,
        parentIsRoot: false,
        parentLabel: null,
        parentLanguageId: null,
        parentLexicalEntryId: null,
        parentUrl: null,
        parentWord: null,
        source: null,
        ...overrides,
    };
}

/** Steps are a flat list (no chain wrapper); depth 0 marks the first step of each hypothesis. */
function stepsAtDepth(container: HTMLElement, depth: number) {
    return Array.from(container.querySelectorAll<HTMLElement>('.DerivationStepList--step'))
        .filter((el) => el.style.getPropertyValue('--ed-derivation-depth') === String(depth));
}

describe('apps/book-browser/components/GlossaryEntities/LexicalEntryDerivations', () => {
    test('renders nothing when there are no derivations', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[]} />);
        expect(container.querySelector('.LexicalEntryDerivations')).toBeFalsy();
    });

    test('links a resolved parent and leaves an unresolved one as plain text', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ groupUuid: 'resolved', parentForm: 'KIRIS', parentUrl: '/wt/123' })],
            [makeStep({ groupUuid: 'unresolved', parentForm: 'ris', parentUrl: null, isUncertain: true })],
        ]} />);

        const link = container.querySelector('a[href="/wt/123"]');
        expect(link?.textContent).toBe('KIRIS');

        const roots = stepsAtDepth(container, 0);
        expect(roots).toHaveLength(2);
        expect(roots[1].querySelector('a')).toBeFalsy();
        expect(roots[1].textContent).toContain('ris');
    });

    test('renders a rejected hypothesis with the rejected marker', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ isRejected: true, source: 'SA/kir', comment: 'Superseded etymology' })],
        ]} />);

        expect(container.querySelector('.DerivationStepList--step.rejected')).toBeTruthy();
        expect(container.querySelector('.LexicalEntryDerivations--note')?.textContent)
            .toBe('SA/kir: Superseded etymology');
    });

    test('renders a multi-step chain in order', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [
                makeStep({ order: 0, parentForm: 'ris' }),
                makeStep({ order: 1, parentForm: 'KIRIS' }),
            ],
        ]} />);

        const steps = container.querySelectorAll('.DerivationStepList--step');
        expect(steps).toHaveLength(2);
        expect(steps[0].textContent).toContain('ris');
        expect(steps[1].textContent).toContain('KIRIS');
    });

    test('renders both branches of a chain even when they share the same order (branching ancestry)', () => {
        // Real data for galadh (id 514703): a linear ancestry gets collapsed by the parser so two
        // distinct ancestors both land on order=1 — confirmed against the live derivations table.
        // Keying steps on `order` alone would collide and corrupt the render; both must still show.
        const { container } = render(<LexicalEntryDerivations derivations={[
            [
                makeStep({ order: 0, parentForm: 'galadā' }),
                makeStep({ order: 1, parentForm: 'galad' }),
                makeStep({ order: 1, parentForm: 'gal' }),
                makeStep({ order: 2, parentForm: 'gal', parentLanguageId: 96 }),
            ],
        ]} />);

        const steps = container.querySelectorAll('.DerivationStepList--step');
        expect(steps).toHaveLength(4);
        expect(steps[1].textContent).toContain('galad');
        expect(steps[2].textContent).toContain('gal');
    });

    test('attaches the citation note to the first step of a multi-step chain, not the rest', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [
                makeStep({ order: 0, parentForm: 'ris', source: 'SA/kir' }),
                makeStep({ order: 1, parentForm: 'KIRIS' }),
            ],
        ]} />);

        const steps = container.querySelectorAll('.DerivationStepList--step');
        expect(steps).toHaveLength(2);
        expect(steps[0].querySelector('.LexicalEntryDerivations--note')?.textContent).toBe('SA/kir');
        expect(steps[1].querySelector('.LexicalEntryDerivations--note')).toBeFalsy();
    });

    test('collapses citations with an identical chain into one, listing every source', () => {
        // Eldamo records one hypothesis per citation, so the same ancestry (e.g. galadā, direct
        // parent of galadh) is frequently attested by several independent sources.
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ groupUuid: 'a', parentForm: 'galadā', source: 'Let/426' })],
            [makeStep({ groupUuid: 'b', parentForm: 'galadā', source: 'PE17/025' })],
            [makeStep({ groupUuid: 'c', parentForm: 'galadā', source: 'PE17/050' })],
        ]} />);

        const roots = stepsAtDepth(container, 0);
        expect(roots).toHaveLength(1);

        expect(roots[0].querySelector('.LexicalEntryDerivations--note')?.textContent).toBe('Let/426; PE17/025; PE17/050');
    });

    test('keeps distinct chains separate even when the immediate parent form matches', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ groupUuid: 'a', parentForm: 'galadā', isUncertain: false, source: 'Let/426' })],
            [makeStep({ groupUuid: 'b', parentForm: 'galadā', isUncertain: true, source: 'PE17/153' })],
        ]} />);

        expect(stepsAtDepth(container, 0)).toHaveLength(2);
    });

    test('groups citations by resolved parent identity even when spelling/stages differ (mallorn)', () => {
        // Real data for mallorn (external_id 3764144369): 3 citations for "OS. malthorn", all
        // resolving to the same parent_lexical_entry_id but with different cited spellings, and
        // only one carrying its own intermediate stage.
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({
                groupUuid: 'a', parentForm: 'malh-orn', parentWord: 'malthorn', parentLexicalEntryId: 521808, source: 'PE17/50',
            })],
            [makeStep({
                groupUuid: 'b', parentForm: 'malh-orn', parentWord: 'malthorn', parentLexicalEntryId: 521808, source: 'PE17/50',
            })],
            [makeStep({
                groupUuid: 'c', parentForm: 'malþorn', parentWord: 'malthorn', parentLexicalEntryId: 521808, source: 'VT42/27',
                intermediateStages: ['malhorn'],
            })],
        ]} />);

        const roots = stepsAtDepth(container, 0);
        expect(roots).toHaveLength(1);

        const note = roots[0].querySelector(':scope > .LexicalEntryDerivations--note');
        expect(note?.textContent).toBe('PE17/50 (malh-orn); PE17/50 (malh-orn); VT42/27 (malþorn)');
    });

    test('renders a citation\'s intermediate stages as a nested chain ending at its cited form', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({
                parentForm: 'malt-ornē', parentWord: 'maltornē', source: 'PE23/140', intermediateStages: ['malþorn'],
            })],
        ]} />);

        const nested = container.querySelector('.LexicalEntryDerivations--intermediate-chain');
        expect(nested).toBeTruthy();
        expect(nested?.textContent).toContain('malþorn');
        expect(nested?.textContent).toContain('malt-ornē');
        expect(nested?.textContent).toContain('PE23/140');
    });

    test('shows the cited spelling in parens only when it differs from the canonical word', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({
                groupUuid: 'differs', parentForm: 'malt-ornē', parentWord: 'maltornē', source: 'PE23/140',
            })],
        ]} />);
        expect(container.querySelector('.LexicalEntryDerivations--note')?.textContent).toBe('PE23/140 (malt-ornē)');

        const { container: sameContainer } = render(<LexicalEntryDerivations derivations={[
            [makeStep({
                groupUuid: 'same', parentForm: 'KIRIS', parentWord: 'KIRIS', source: 'SA/kir',
            })],
        ]} />);
        expect(sameContainer.querySelector('.LexicalEntryDerivations--note')?.textContent).toBe('SA/kir');
    });

    test('renders the canonical word, gloss, and reconstructed marker on each step', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({
                parentForm: 'malt-ornē', parentWord: 'maltornē', parentGloss: 'gold-tree', parentLabel: 'Reconstructed',
                source: 'PE23/140',
            })],
        ]} />);

        const link = container.querySelector('.DerivationStepList--step span.reconstructed');
        expect(link?.textContent).toBe('maltornē');
        expect(container.querySelector('.LexicalEntryDerivations--gloss')?.textContent).toBe('gold-tree');
    });

    test('resolves the language badge from the LanguageLookupProvider for an ordinary ancestor', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[{ id: 35, name: 'Old Sindarin', shortName: 'os' }]}>
                <LexicalEntryDerivations derivations={[[makeStep({ parentForm: 'galadā', parentLanguageId: 35 })]]} />
            </LanguageLookupProvider>,
        );

        expect(container.querySelector('.LexicalEntryDerivations--language')?.textContent).toBe('OS.');
        expect(container.querySelector('.RootForm')).toBeFalsy();
        expect(stepsAtDepth(container, 0)[0].textContent).toContain('galadā');
    });

    test('renders no language badge when the language id cannot be resolved', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[[makeStep({ parentLanguageId: 999 })]]} />);
        expect(container.querySelector('.LexicalEntryDerivations--language')).toBeFalsy();
    });

    test('renders a root ancestor via RootForm: uppercase spelling with a "√" prefix', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ parentForm: 'GAL', parentWord: 'gal', parentIsRoot: true })],
        ]} />);

        const root = container.querySelector('.RootForm');
        expect(root?.textContent).toBe('√GAL');
    });

    test('shows the language badge in front of a root, same as any other ancestor', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[{ id: 96, name: 'Middle Primitive Elvish', shortName: 'mp', mark: 'M' }]}>
                <LexicalEntryDerivations derivations={[
                    [makeStep({ parentForm: 'GAL', parentIsRoot: true, parentLanguageId: 96 })],
                ]} />
            </LanguageLookupProvider>,
        );

        expect(container.querySelector('.LexicalEntryDerivations--language')?.textContent).toBe('MP.');
        expect(container.querySelector('.RootForm')?.textContent).toBe('M√GAL');
    });

    test('root uses its own cited spelling (parentForm), not the resolved entry\'s plain-cased word', () => {
        const { container } = render(<LexicalEntryDerivations derivations={[
            [makeStep({ parentForm: 'gal', parentWord: 'gal', parentIsRoot: true })],
        ]} />);

        expect(container.querySelector('.RootForm')?.textContent).toBe('√GAL');
    });

    test('marks a root with whatever superscript is configured on its language', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[
                { id: 96, name: 'Middle Primitive Elvish', shortName: 'mp', mark: 'M' },
                { id: 20, name: 'Primitive elvish', shortName: 'p', mark: null },
            ]}>
                <LexicalEntryDerivations derivations={[
                    [makeStep({ groupUuid: 'middle', parentForm: 'GAL', parentIsRoot: true, parentLanguageId: 96 })],
                    [makeStep({ groupUuid: 'late', parentForm: 'GALAD', parentIsRoot: true, parentLanguageId: 20 })],
                ]} />
            </LanguageLookupProvider>,
        );

        const roots = container.querySelectorAll('.RootForm');
        expect(roots[0].querySelector('.RootForm--mark')?.textContent).toBe('M');
        expect(roots[1].querySelector('.RootForm--mark')).toBeFalsy();
    });
});
