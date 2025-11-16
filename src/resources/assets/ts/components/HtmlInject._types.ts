import type { ReactNode } from 'react';
import type { ComponentEventHandler } from './Component._types';

export interface IReferenceLinkClickDetails {
    languageShortName: string;
    normalizedWord: string;
    word: string;
    updateBrowserHistory?: boolean;
    lexicalEntryId?: number;
}

export interface IEventProps {
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
}

export interface IProps extends IEventProps {
    html: string;
}

export interface IState {
    elements: ReactNode;
    lastHtml: string;
}
