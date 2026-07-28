import { beforeAll, describe, expect, test } from '@jest/globals';
import { fireEvent, render } from '@testing-library/react';

import { GlobalEventLoadReference } from '@root/config';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import { setInstance } from '@root/di';
import { DI } from '@root/di/keys';
import ReferenceLink from './ReferenceLink';

describe('apps/book-browser/components/GlossaryEntities/ReferenceLink', () => {
    beforeAll(() => {
        setInstance(DI.GlobalEvents, GlobalEventConnector);
    });

    test('renders as plain text with no link when there is no url', () => {
        const { container } = render(<ReferenceLink lexicalEntryId={123} url={null}>gal</ReferenceLink>);

        expect(container.querySelector('a')).toBeFalsy();
        expect(container.querySelector('span')?.textContent).toBe('gal');
    });

    test('renders an anchor pointing at the given url', () => {
        const { container } = render(<ReferenceLink lexicalEntryId={123} url="/wt/123">gal</ReferenceLink>);

        const link = container.querySelector('a');
        expect(link?.getAttribute('href')).toBe('/wt/123');
        expect(link?.textContent).toBe('gal');
    });

    test('a plain click fires loadReference with the lexicalEntryId instead of navigating', () => {
        const { container } = render(<ReferenceLink lexicalEntryId={123} url="/wt/123">gal</ReferenceLink>);
        const link = container.querySelector('a');

        const received: number[] = [];
        const onLoadReference = (ev: Event) => received.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        const clickResult = fireEvent.click(link, { button: 0 });

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(received).toEqual([123]);
        // fireEvent.click returns false when the event's preventDefault() was called.
        expect(clickResult).toBe(false);
    });

    test('a modified click (e.g. ctrl+click to open in a new tab) is left alone', () => {
        const { container } = render(<ReferenceLink lexicalEntryId={123} url="/wt/123">gal</ReferenceLink>);
        const link = container.querySelector('a');

        const received: number[] = [];
        const onLoadReference = (ev: Event) => received.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        const clickResult = fireEvent.click(link, { button: 0, ctrlKey: true });

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(received).toEqual([]);
        expect(clickResult).toBe(true);
    });

    test('does not fire loadReference when there is no lexicalEntryId to load', () => {
        const { container } = render(<ReferenceLink lexicalEntryId={null} url="/wt/123">gal</ReferenceLink>);
        const link = container.querySelector('a');

        const received: number[] = [];
        const onLoadReference = (ev: Event) => received.push((ev as CustomEvent).detail.lexicalEntryId);
        window.addEventListener(GlobalEventLoadReference, onLoadReference);

        fireEvent.click(link, { button: 0 });

        window.removeEventListener(GlobalEventLoadReference, onLoadReference);

        expect(received).toEqual([]);
    });
});
