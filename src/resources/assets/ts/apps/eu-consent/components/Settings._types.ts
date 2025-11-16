import type { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    consentedUseCases: string[];
    onConsentedUseCasesChange: ComponentEventHandler<string[]>;
}
