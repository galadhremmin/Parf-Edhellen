import { ComponentEventHandler } from './Component._types';

export interface IProps {
    html: string;
    onReferenceLinkClick?: ComponentEventHandler<{
        languageShortName: string;
        word: string;
    }>;
}

export interface IState {
    htmlHashCode: number;
}
