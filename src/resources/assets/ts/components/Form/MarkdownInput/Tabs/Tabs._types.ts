import type { ComponentEventHandler } from '@root/components/Component._types';
import { Tab } from '../MarkdownInput._types';

export interface IProps {
    onTabChange: ComponentEventHandler<Tab>;
    tab: Tab;
}
