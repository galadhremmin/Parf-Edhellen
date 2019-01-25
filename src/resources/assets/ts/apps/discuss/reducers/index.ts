import { combineReducers } from 'redux';

import { DeriveRootReducer } from '@root/_types';

import { default as pagination } from './PostPaginationReducer';
import { default as posts } from './PostsReducer';
import { default as thread } from './ThreadReducer';

const reducers = {
    pagination,
    posts,
    thread,
};

export type RootReducer = DeriveRootReducer<typeof reducers>;

export default combineReducers(reducers);
