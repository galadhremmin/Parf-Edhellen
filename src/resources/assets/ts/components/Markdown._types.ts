export interface IProps {
    parse: boolean;
    text: string;
}

export interface IState {
    dirty: boolean;
    html: string;
    lastText: string;
}
