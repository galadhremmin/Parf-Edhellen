import type IGlobalEvents from '@root/connectors/IGlobalEvents';
import type { IRelatedConcept } from '@root/connectors/backend/IBookApi';

export interface IProps {
    concepts: IRelatedConcept[];

    globalEvents?: IGlobalEvents;

    /** What the row is: "Kinds of tree", or "A kind of". */
    heading: string;

    /** How many to show before the rest are hidden behind "more". */
    initialCount?: number;
}
