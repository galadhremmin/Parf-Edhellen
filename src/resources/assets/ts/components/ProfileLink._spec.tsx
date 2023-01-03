import { render, screen } from '@testing-library/react';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import React from 'react';

import { IAccountEntity } from '../connectors/backend/IGlossResourceApi';
import ProfileLink from './ProfileLink';

describe('components/ProfileLink', () => {
    const SampleAccount = {
        id: Math.floor(Math.random() * 1000),
        nickname: 'Glorfindel',
    } as Partial<IAccountEntity>;

    test('renders a clickable link', async () => {
        render(<ProfileLink account={SampleAccount as any} />);

        const link = await screen.findAllByRole('link');
        expect(link.length).toEqual(1);
        
        expect(link[0].getAttribute('href')).toEqual(`/author/${SampleAccount.id}`);
        expect(link[0].getAttribute('title')).toEqual(`View ${SampleAccount.nickname}'s profile`);
        expect(link[0].textContent).toEqual(SampleAccount.nickname);
    });

    test('supports `className` attribute', async () => {
        const className = 'test';

        render(<ProfileLink account={SampleAccount as any} className={className} />);

        const link = await screen.findByRole('link');
        expect(link.classList.contains(className)).toBeTruthy();
    });

    test('gracefully handles erroneous input', () => {
        const badInput = [ null, undefined, 1, 'string', 1.521 ];

        for (const account of badInput) {
            const { container } = render(<ProfileLink account={account as any} />);
            expect(container.children.length).toEqual(0);
        }
    });
});
