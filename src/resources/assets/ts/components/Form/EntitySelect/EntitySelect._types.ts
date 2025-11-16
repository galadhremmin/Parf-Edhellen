import type { ReactNode } from 'react';

import type { ComponentEventHandler } from '../../Component._types';
import type { IComponentProps } from '../FormComponent._types';

export interface IProps<T> extends IComponentProps<T> {
    formatter?: (suggestion: T) => string;
    loading?: boolean;
    onClearSuggestions: ComponentEventHandler<void>;
    onSuggest: ComponentEventHandler<string>;
    renderSuggestion: (suggestion: T) => ReactNode;
    renderValue: (value: T) => ReactNode;
    suggestions: T[];
    valueClassNames: string;
}

export interface IState {
    editing: boolean;
    focused: boolean;
    text: string;
    suggestionsFor: string;
}
