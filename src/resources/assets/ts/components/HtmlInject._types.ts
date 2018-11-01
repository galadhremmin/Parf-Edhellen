import { ComponentEventHandler } from './Component._types';

export interface IEventProps {
    onReferenceLinkClick?: ComponentEventHandler<{
        languageShortName: string;
        word: string;
    }>;
}

export interface IProps extends IEventProps {
    html: string;
}

export interface IState {
    htmlHashCode: number;
}
