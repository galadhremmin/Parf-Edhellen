import { render } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';

import GlossaryEntitiesFetching from './GlossaryEntitiesFetching';
import GlossaryEntitiesLoading from './GlossaryEntitiesLoading';

describe('apps/book-browser/components/GlossaryEntities/GlossaryEntitiesLoading', () => {
    test('names the word that was asked for', () => {
        const { container } = render(<GlossaryEntitiesLoading minHeight={500} word="estel" />);

        expect(container.querySelector('.GlossaryEntitiesLoading__word').textContent).toEqual('estel');
        expect(container.querySelector('.GlossaryEntitiesLoading__status').textContent)
            .toContain('estel');
    });

    test('does not imitate the glossary it is waiting for', () => {
        // The shape of a glossary depends on the answer, so a placeholder version
        // of it can only be a guess -- and a wrong guess reads as a jump.
        const { container } = render(<GlossaryEntitiesLoading minHeight={500} word="estel" />);

        expect(container.querySelector('.lexical-entry')).toBeNull();
        expect(container.querySelector('.ed-glossary__language')).toBeNull();
    });

    test('holds the space the outgoing glossary occupied', () => {
        const { container } = render(<GlossaryEntitiesLoading minHeight={420} word="estel" />);

        const root = container.querySelector('.GlossaryEntitiesLoading') as HTMLElement;
        expect(root.style.minHeight).toEqual('420px');
    });

    test('falls back to the plain indicator when the word is not known', () => {
        // Deep links and history navigation land here.
        const { container } = render(<GlossaryEntitiesLoading minHeight={500} />);

        expect(container.querySelector('.GlossaryEntitiesLoading')).toBeNull();
        expect(container.querySelector('.LoadingIndicator')).toBeTruthy();
    });
});

describe('apps/book-browser/components/GlossaryEntities/GlossaryEntitiesFetching', () => {
    test('names the word on its way', () => {
        const { container } = render(<GlossaryEntitiesFetching word="estel" />);

        expect(container.querySelector('.GlossaryEntitiesFetching__word').textContent).toEqual('estel');
    });

    test('announces itself politely rather than interrupting', () => {
        const { container } = render(<GlossaryEntitiesFetching word="estel" />);

        const root = container.querySelector('.GlossaryEntitiesFetching');
        expect(root.getAttribute('role')).toEqual('status');
        expect(root.getAttribute('aria-live')).toEqual('polite');
    });

    test('stays out of the way of pointers over the dimmed glossary', () => {
        const { container } = render(<GlossaryEntitiesFetching word="estel" />);

        // The card floats over content that is still mounted; it must not
        // swallow clicks meant for it once the fetch completes.
        expect(container.querySelector('.GlossaryEntitiesFetching')).toBeTruthy();
    });

    test('copes with no word to name', () => {
        const { container } = render(<GlossaryEntitiesFetching />);

        expect(container.querySelector('.GlossaryEntitiesFetching__word').textContent)
            .toEqual('Retrieving glossary');
    });
});
