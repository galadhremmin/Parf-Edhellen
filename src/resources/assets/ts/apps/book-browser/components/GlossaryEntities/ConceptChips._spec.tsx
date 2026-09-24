import { describe, expect, jest, test } from '@jest/globals';
import { fireEvent, render, screen } from '@testing-library/react';

import GlobalEventConnector from '@root/connectors/GlobalEventConnector';

import ConceptChips from './ConceptChips';

const concepts = [
    { entries: 35, label: 'elm' },
    { entries: 34, label: 'beech' },
    { entries: 2, label: 'silver birch' },
];

function globalEventsSpy() {
    const globalEvents = new GlobalEventConnector();
    const fire = jest.fn();
    globalEvents.fire = fire as any;

    return { fire, globalEvents };
}

describe('apps/book-browser/components/GlossaryEntities/ConceptChips', () => {
    test('names the row it is', () => {
        render(<ConceptChips concepts={concepts} globalEvents={globalEventsSpy().globalEvents} heading="Kinds of tree" />);

        expect(screen.getByText('Kinds of tree')).toBeTruthy();
        expect(screen.getByRole('link', { name: 'elm 35' })).toBeTruthy();
    });

    test('links to the dictionary page of the kind, spaces and all', () => {
        render(<ConceptChips concepts={concepts} globalEvents={globalEventsSpy().globalEvents} heading="Kinds of tree" />);

        expect(screen.getByRole('link', { name: 'beech 34' }).getAttribute('href')).toBe('/w/beech');
        expect(screen.getByRole('link', { name: 'silver birch 2' }).getAttribute('href')).toBe('/w/silver%20birch');
    });

    test('opens the kind in the glossary rather than reloading the page', () => {
        const { fire, globalEvents } = globalEventsSpy();
        render(<ConceptChips concepts={concepts} globalEvents={globalEvents} heading="Kinds of tree" />);

        fireEvent.click(screen.getByRole('link', { name: 'beech 34' }), { button: 0 });

        expect(fire).toHaveBeenCalledWith(globalEvents.loadReference, {
            languageShortName: null,
            normalizedWord: 'beech',
            word: 'beech',
        });
    });

    test('leaves a click meant for a new tab to the browser', () => {
        const { fire, globalEvents } = globalEventsSpy();
        render(<ConceptChips concepts={concepts} globalEvents={globalEvents} heading="Kinds of tree" />);

        fireEvent.click(screen.getByRole('link', { name: 'beech 34' }), { button: 0, metaKey: true });

        expect(fire).not.toHaveBeenCalled();
    });

    test('keeps the rest behind a count until asked', () => {
        render(<ConceptChips concepts={concepts} globalEvents={globalEventsSpy().globalEvents} initialCount={2} heading="Kinds of tree" />);

        expect(screen.queryByRole('link', { name: 'silver birch 2' })).toBeNull();
        fireEvent.click(screen.getByRole('button', { name: '1 more…' }));

        expect(screen.getByRole('link', { name: 'silver birch 2' })).toBeTruthy();
    });

    test('renders nothing when the word has no kinds', () => {
        const { container } = render(<ConceptChips concepts={[]} globalEvents={globalEventsSpy().globalEvents} heading="Kinds of oak" />);

        expect(container.firstChild).toBeNull();
    });
});
