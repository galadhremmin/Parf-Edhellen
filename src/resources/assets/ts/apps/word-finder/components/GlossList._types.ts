import IGlobalEvents from '@root/connectors/IGlobalEvents';
import { IGameGloss } from '../reducers/IGlossesReducer';

export interface IProps {
    glosses: IGameGloss[];
    tengwarMode: string;
    globalEvents?: IGlobalEvents;
}
