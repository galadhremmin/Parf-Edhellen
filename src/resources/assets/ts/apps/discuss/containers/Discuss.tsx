import React from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Pagination from '@root/components/Pagination';
import Post from '../components/Post';

import { IProps } from '../index._types';

export default class Discuss extends React.PureComponent<IProps> {
    public render() {
        const data = this.props.discussData;
        if (!data || ! Array.isArray(data.posts)) {
            return null;
        }

        return <React.Fragment>
            {data.posts.map((post) => <Post post={post} key={post.id} />)};
            <Pagination currentPage={data.currentPage}
                noOfPages={data.noOfPages}
                onClick={this._onNavigateToPage}
                pages={data.pages}
            />
        </React.Fragment>;
    }

    private _onNavigateToPage = (ev: IComponentEvent<number>) => {
        const pageNumber = ev.value;
    }
}
