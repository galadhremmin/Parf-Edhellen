import { combineReducers } from 'redux';

import { CreateRootReducer } from '@root/_types';

import { default as newPosts } from './NewPostsReducer';
import { default as paginations } from './PostPaginationsReducer';
import { default as posts } from './PostsReducer';
import { default as threadMetadatas } from './ThreadMetadatasReducer';
import { default as threads } from './ThreadsReducer';

export { keyGenerator } from './key-generator';

const reducers = {
    newPosts,
    paginations,
    posts,
    threads,
    threadMetadatas,
};

export type RootReducer = CreateRootReducer<typeof reducers>;

export default combineReducers(reducers);
