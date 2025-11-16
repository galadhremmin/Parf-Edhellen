import type IGlobalEvents from '@root/connectors/IGlobalEvents';
import type { IGameGloss } from '../reducers/IGlossesReducer';

export interface IProps {
    glosses: IGameGloss[];
    tengwarMode: string;
    globalEvents?: IGlobalEvents;
}
