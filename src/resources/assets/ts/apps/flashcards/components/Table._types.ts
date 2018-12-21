import { ReactNode } from 'react';
import { SideOfTheCard } from './Card._types';

export interface IProps {
    children: ReactNode;
    cardState: SideOfTheCard;
}
