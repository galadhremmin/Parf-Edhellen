export const enum LikeState {
    Default = 0,
    Liked = 1,
}

export interface IProps {
    numberOfLikes: number;
    state: LikeState;
}
