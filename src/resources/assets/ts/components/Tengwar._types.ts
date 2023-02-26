export interface ITranscriber {
    transcribe(text: string, mode: string): Promise<string>;
    getModeName(mode: string): Promise<string>;
}

export interface IProps {
    as?: keyof JSX.IntrinsicElements;
    mode?: string;
    text: string;
    transcribe?: boolean;
    transcriber?: ITranscriber;
}

export interface IState {
    modeName?: string;
    lastText: string;
    transcribed: string;
}
