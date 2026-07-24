import { describe, expect, test } from '@jest/globals';
import { render } from '@testing-library/react';

import { DerivationStep, DerivationStepList } from './DerivationStepList';

describe('apps/book-browser/components/GlossaryEntities/DerivationStepList', () => {
    test('renders a ul wrapper and li steps with the shared class names', () => {
        const { container } = render(<DerivationStepList>
            <DerivationStep depth={0}>first</DerivationStep>
            <DerivationStep depth={1}>second</DerivationStep>
        </DerivationStepList>);

        expect(container.querySelector('ul.DerivationStepList')).toBeTruthy();
        const steps = container.querySelectorAll<HTMLElement>('li.DerivationStepList--step');
        expect(steps).toHaveLength(2);
        expect(steps[0].textContent).toBe('first');
        expect(steps[1].textContent).toBe('second');
    });

    test('exposes depth via the --ed-derivation-depth custom property', () => {
        const { container } = render(<DerivationStepList>
            <DerivationStep depth={3}>x</DerivationStep>
        </DerivationStepList>);

        const step = container.querySelector<HTMLElement>('.DerivationStepList--step');
        expect(step?.style.getPropertyValue('--ed-derivation-depth')).toBe('3');
    });

    test('merges a caller-supplied className with the shared step class', () => {
        const { container } = render(<DerivationStepList>
            <DerivationStep depth={0} className="rejected">x</DerivationStep>
        </DerivationStepList>);

        expect(container.querySelector('.DerivationStepList--step.rejected')).toBeTruthy();
    });
});
