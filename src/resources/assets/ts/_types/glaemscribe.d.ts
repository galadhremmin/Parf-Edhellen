declare module 'glaemscribe/*';

interface ITranscriber {
    human_name: string;
    transcribe(text: string, charset: string): string[];
}

interface ILoadedModes {
    [mode: string]: ITranscriber;
}

interface ILoadedCharsets {
    [charset: string]: string;
}

interface IResourceManager {
    loaded_charsets: ILoadedCharsets;
    loaded_modes: ILoadedModes;

    load_charsets(charset: string): void;
    load_modes(mode: string): void;
}

interface IGlaemscribe {
    resource_manager: IResourceManager;
}

declare const Glaemscribe: IGlaemscribe;
