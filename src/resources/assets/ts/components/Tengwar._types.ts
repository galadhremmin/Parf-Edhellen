export interface ITranscriber {
    transcribe(text: string, mode: string): string;
}

export interface IProps {
    as?: string | React.ComponentType;
    mode?: string;
    text: string;
    transcribe?: boolean;
    transcriber?: ITranscriber;
}

export interface IState {
    lastText: string;
    transcribed: string;
}
