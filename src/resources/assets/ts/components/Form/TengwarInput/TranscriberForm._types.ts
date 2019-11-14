import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    languageId: number;
    text: string;
    transcription: string;
    onTranscription: ComponentEventHandler<ITranscription>;
}

export interface ITranscription {
    text: string;
    transcription: string;
}
