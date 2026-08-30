import classNames from '@root/utilities/ClassNames';
import { SideOfTheCard } from './Card._types';
import type { IProps } from './Table._types';

import './FlipCard.scss';

const Table = (props: IProps) => <article className={classNames('flip-container', {
        flipped: props.cardState === SideOfTheCard.Back,
    })}>
    <div className="flipper">
        {props.children}
    </div>
</article>;

export default Table;
