import { ComponentEventHandler } from './Component._types';

export interface IReferenceLinkClickDetails {
    languageShortName: string;
    normalizedWord: string;
    word: string;
    updateBrowserHistory?: boolean;
}

export interface IEventProps {
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
}

export interface IProps extends IEventProps {
    html: string;
}

export interface IState {
    elements: React.ReactNode;
    lastHtml: string;
}
