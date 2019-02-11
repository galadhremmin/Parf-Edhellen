import { ComponentEventHandler } from "@root/components/Component._types";

export interface IFormOutput {
    content: string;
    subject: string;
}

export interface IProps extends Partial<IFormOutput> {
    subjectEnabled?: boolean;

    onCancel?: ComponentEventHandler<void>;
    onSubmit?: ComponentEventHandler<IFormOutput>;
}
