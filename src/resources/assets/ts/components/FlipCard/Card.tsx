import type { IProps } from './Card._types';
import { SideOfTheCard } from './Card._types';

const classNameFromSideOfCard = (side: SideOfTheCard) => {
    switch (side) {
        case SideOfTheCard.Front:
            return 'front';
        case SideOfTheCard.Back:
            return 'back';
    }

    return null;
};

const Card = (props: IProps) => <section className={classNameFromSideOfCard(props.side)}>
    {props.children}
</section>;

export default Card;
