import { describe, expect, test } from '@jest/globals';
import { render } from '@testing-library/react';

import RootForm from './RootForm';
import { LanguageLookupProvider } from './LanguageLookupContext';

describe('apps/book-browser/components/GlossaryEntities/RootForm', () => {
    test('renders the form uppercase with a "√" prefix', () => {
        const { container } = render(<RootForm form="gal" />);
        expect(container.querySelector('.RootForm')?.textContent).toBe('√GAL');
    });

    test('links to the resolved entry when a url is given', () => {
        const { container } = render(<RootForm form="GAL" url="/wt/493902" />);
        const link = container.querySelector('a.RootForm');
        expect(link).toBeTruthy();
        expect(link?.getAttribute('href')).toBe('/wt/493902');
    });

    test('renders as plain text (no link) when there is no url', () => {
        const { container } = render(<RootForm form="GAL" />);
        expect(container.querySelector('a.RootForm')).toBeFalsy();
        expect(container.querySelector('span.RootForm')).toBeTruthy();
    });

    test('shows the superscript mark configured on the language', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[
                { id: 96, name: 'Middle Primitive Elvish', shortName: 'mp', mark: 'M' },
            ]}>
                <RootForm form="GALAD" languageId={96} />
            </LanguageLookupProvider>,
        );
        expect(container.querySelector('.RootForm--mark')?.textContent).toBe('M');
    });

    test('shows whatever mark is configured, not a hardcoded letter', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[
                { id: 101, name: 'Early Primitive Elvish', shortName: 'ep', mark: 'E' },
            ]}>
                <RootForm form="KALA" languageId={101} />
            </LanguageLookupProvider>,
        );
        expect(container.querySelector('.RootForm--mark')?.textContent).toBe('E');
    });

    test('shows no mark when the language has none configured (the default)', () => {
        const { container } = render(
            <LanguageLookupProvider languages={[
                { id: 20, name: 'Primitive elvish', shortName: 'p', mark: null },
            ]}>
                <RootForm form="GAL" languageId={20} />
            </LanguageLookupProvider>,
        );
        expect(container.querySelector('.RootForm--mark')).toBeFalsy();
    });

    test('shows no mark when the language id cannot be resolved', () => {
        const { container } = render(<RootForm form="GAL" languageId={999} />);
        expect(container.querySelector('.RootForm--mark')).toBeFalsy();
    });
});
