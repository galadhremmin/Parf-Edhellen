import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';

import { default as newPost } from './NewPostReducer';
import { default as pagination } from './PostPaginationReducer';
import { default as posts } from './PostsReducer';
import { default as thread } from './ThreadReducer';

const reducers = {
    newPost,
    pagination,
    posts,
    thread,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
