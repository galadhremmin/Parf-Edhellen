import Cache from '@root/utilities/Cache';

import { ComponentEventHandler } from './Component._types';

export interface IComponentProps {
    [propName: string]: any;
}

export interface IComponentConfig {
    enter2Paragraph: boolean;
}

export interface IProps {
    className?: string;
    configCacheFactory?: () => Cache<IComponentConfig>;
    id?: string;
    onChange?: ComponentEventHandler<string>;
    name: string;
    required?: boolean;
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
