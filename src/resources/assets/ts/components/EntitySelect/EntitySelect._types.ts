import { ReactNode } from 'react';

import { ComponentEventHandler } from '../Component._types';
import { IComponentProps } from '../FormComponent._types';

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
