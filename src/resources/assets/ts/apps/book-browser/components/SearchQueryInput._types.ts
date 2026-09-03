import type { IComponentEvent } from '@root/components/Component._types';
import type { IComponentProps } from '@root/components/Form/FormComponent._types';

export interface IProps extends IComponentProps<string> {
    autoFocus?: boolean;
    loading?: boolean;
    onSearchResultNavigate?: (ev: IComponentEvent<number>) => void;
    /** Invoked when the user accepts the suggestion currently advertised by the field. */
    onSuggestionSelect?: (ev: IComponentEvent<string>) => void;
    showEnterHint?: boolean;
}
