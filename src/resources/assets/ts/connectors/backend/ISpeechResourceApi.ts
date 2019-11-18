export interface ISpeechEntity {
    id: number;
    name: string;
}

export default interface ISpeechResourceApi {
    speeches(): Promise<ISpeechEntity[]>;
}
