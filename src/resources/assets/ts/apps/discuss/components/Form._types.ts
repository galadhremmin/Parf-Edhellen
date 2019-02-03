export interface IState {
    content: string;
    subject: string;
}
export interface IProps extends IState {
    subjectEnabled: boolean;
}
