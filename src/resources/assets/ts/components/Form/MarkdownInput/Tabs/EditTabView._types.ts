import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    enter2Paragraph?: boolean;
    onEnter2ParagraphChange?: ComponentEventHandler<boolean>;

    id?: string;
    onChange?: ComponentEventHandler<string>;
    name: string;
    required?: boolean;
    rows?: number;
    value: string;
}
