import { ComponentEventHandler } from './Component._types';

export interface IComponentProps {
    [propName: string]: any;
}

export interface IProps {
    className?: string;
    id?: string;
    onChange?: ComponentEventHandler<string>;
    name: string;
    rows?: number;
    value: string;
}

export const enum Tab {
    EditTab = 0,
    PreviewTab = 1,
    SyntaxTab = 2,
}

export interface IState {
    currentTab: Tab;
    enter2Paragraph: boolean;
}
