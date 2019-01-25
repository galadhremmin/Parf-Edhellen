import React from 'react';
import { connect } from 'react-redux';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Pagination from '@root/components/Pagination';

import DiscussActions from '../actions/DiscussActions';
import Post from '../components/Post';
import { IProps } from '../index._types';
import { RootReducer } from '../reducers';

export class Discuss extends React.PureComponent<IProps> {
    public render() {
        const {
            currentPage,
            noOfPages,
            pages,
            posts,
        } = this.props;

        if (! Array.isArray(posts)) {
            return null;
        }

        return <React.Fragment>
            {posts.map((post) => <Post post={post} key={post.id} />)};
            <Pagination currentPage={currentPage}
                noOfPages={noOfPages}
                onClick={this._onNavigateToPage}
                pages={pages}
            />
        </React.Fragment>;
    }

    private _onNavigateToPage = (ev: IComponentEvent<number>) => {
        const pageNumber = ev.value;
        const {
            currentPage,
            onPageChange,
            thread,
        } = this.props;

        if (pageNumber !== currentPage) {
            fireEvent(this, onPageChange, {
                pageNumber,
                thread,
            });
        }
    }
}

const mapStateToProps = (state: RootReducer) => ({
    ...state.pagination,
    posts: state.posts,
    thread: state.thread,
} as Partial<IProps>);

const actions = new DiscussActions();
const mapDispatchToProps = (dispatch: any) => ({
    onPageChange: (ev) => dispatch(actions.thread({
        entityId: ev.value.thread.entityId,
        entityType: ev.value.thread.entityType,
        id: ev.value.thread.id,
        offset: ev.value.pageNumber,
    }))
} as Partial<IProps>);

export default connect(mapStateToProps, mapDispatchToProps)(Discuss);
