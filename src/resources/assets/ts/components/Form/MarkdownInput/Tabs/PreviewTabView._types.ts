import { IProps as IMarkdownProps } from '@root/components/Markdown._types';

export interface IProps extends Partial<Pick<IMarkdownProps, 'markdownApi'>> {
    value: string;
}
