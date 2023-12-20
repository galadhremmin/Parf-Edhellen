import { ComponentEventHandler } from '@root/components/Component._types';
import IUtilityApi from '@root/connectors/backend/IUtilityApi';

export interface IProps {
    enter2Paragraph?: boolean;
    onEnter2ParagraphChange?: ComponentEventHandler<boolean>;
    markdownApi?: IUtilityApi;

    id?: string;
    onChange?: ComponentEventHandler<string>;
    name: string;
    required?: boolean;
    rows?: number;
    value: string;
}
