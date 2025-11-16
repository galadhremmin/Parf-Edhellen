import type { ComponentEventHandler } from '@root/components/Component._types';

export interface IFormChangeData {
    name: string;
    value: string;
}

export interface IFormOutput {
    content: string;
    subject: string;
}

export interface IProps extends Partial<IFormOutput> {
    name: string;
    subjectEnabled?: boolean;

    onCancel?: ComponentEventHandler<void>;
    onChange?: ComponentEventHandler<IFormChangeData>;
    onSubmit?: ComponentEventHandler<IFormOutput>;
}
