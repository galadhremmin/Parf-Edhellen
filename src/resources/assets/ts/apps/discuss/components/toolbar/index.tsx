import { IPostEntity } from '@root/connectors/backend/IDiscussApi';
import {
    IRoleManager,
    SecurityRole,
} from '@root/security';

import { IProps } from './index._types';

import DeletePost from './DeletePost';
import EditPost from './EditPost';
import Likes from './Likes';
import MovePost from './MovePost';
import StickyPost from './StickyPost';

import './index.scss';

const getEligibleToolbarComponents = (roleManager: IRoleManager, post: IPostEntity) => {
    const role = roleManager.currentRole;
    const accountId = roleManager.accountId;
    const components = [];

    if (role === SecurityRole.Administrator) {
        if (post._isThreadPost) {
            components.push(StickyPost);
            components.push(MovePost);
        }
        // components.push(RestorePost); -- TODO
    }

    if (role === SecurityRole.Administrator ||
        accountId === post.account.id) {
        components.push(EditPost);
        components.push(DeletePost);
    }

    components.push(Likes);
    return components;
};

function Toolbar(props: IProps) {
    const {
        post,
        roleManager,
    } = props;
    const toolbar = getEligibleToolbarComponents(roleManager, post);

    return <span className="post-header--tools">
        {toolbar.map((Component, i) => <Component key={i} {...props} />)}
    </span>;
}

export default Toolbar;
