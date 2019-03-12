import React, {
    useEffect,
} from 'react';

import {
    IProps,
    LikeState,
} from './Likes._types';

function Likes(props: IProps) {
    const {
        numberOfLikes,
        state,
    } = props;

    return <a href="#">
        {numberOfLikes}
        <span className="glyphicon glyphicon-thumbs-up"></span>
    </a>;
}

Likes.defaultProps = {
    numberOfLikes: 0,
    state: LikeState.Default,
} as Partial<IProps>;

export default Likes;
