import type { IProps as IDialogProps } from './Dialog._types';

export interface IProps extends Pick<IDialogProps<never>, 'open' | 'onDismiss'> {
    featureName?: string;
}
