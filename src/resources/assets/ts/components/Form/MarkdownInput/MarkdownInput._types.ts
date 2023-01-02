import Cache from '@root/utilities/Cache';

import { IProps as IMarkdownProps } from '@root/components/Markdown._types';
import { IProps as IEditTabViewProps } from './Tabs/EditTabView._types';

export interface IComponentProps {
    [propName: string]: any;
}

export interface IComponentConfig {
    enter2Paragraph: boolean;
}

export interface IProps extends IEditTabViewProps, Partial<Pick<IMarkdownProps, 'markdownApi'>> {
    className?: string;
    configCacheFactory?: () => Cache<IComponentConfig>;
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
