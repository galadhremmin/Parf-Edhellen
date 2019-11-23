import { ReactNode } from 'react';

export enum SideOfTheCard {
    Front,
    Back,
}

export interface IProps {
    children: ReactNode;
    side: SideOfTheCard;
}
