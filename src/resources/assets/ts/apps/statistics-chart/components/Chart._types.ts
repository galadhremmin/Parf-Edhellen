export interface IData {
    [nickname: string]: number;
    date: number;
    numberOfItems: 1;
}

export interface IProps {
    data: IData[];
}
