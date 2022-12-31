import { render, screen } from '@testing-library/react';
import { expect } from 'chai';
import React from 'react';

import { IAccountEntity } from '../connectors/backend/IGlossResourceApi';
import ProfileLink from './ProfileLink';

describe('components/ProfileLink', () => {
    const SampleAccount = {
        id: Math.floor(Math.random() * 1000),
        nickname: 'Glorfindel',
    } as Partial<IAccountEntity>;

    it('renders a clickable link', async () => {
        render(<ProfileLink account={SampleAccount as any} />);

        const link = await screen.findAllByRole('link');
        expect(link.length).to.equal(1);
        
        expect(link[0].getAttribute('href')).to.equal(`/author/${SampleAccount.id}`);
        expect(link[0].getAttribute('title')).to.equal(`View ${SampleAccount.nickname}'s profile`);
        expect(link[0].textContent).to.equal(SampleAccount.nickname);
    });

    it('supports `className` attribute', async () => {
        const className = 'test';

        render(<ProfileLink account={SampleAccount as any} className={className} />);

        const link = await screen.findByRole('link');
        expect(link.classList.contains(className)).to.be.true;
    });

    it('gracefully handles erroneous input', () => {
        const badInput = [ null, undefined, 1, 'string', 1.521 ];

        for (const account of badInput) {
            const { container } = render(<ProfileLink account={account as any} />);
            expect(container.children.length).to.equal(0);
        }
    });
});
