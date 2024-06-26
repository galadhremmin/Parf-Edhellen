import { AnonymousAvatarPath } from '@root/config';
import { excludeProps } from '@root/utilities/func/props';
import { IProps } from './Avatar._types';

import './Avatar.scss';

function Avatar(props: IProps) {
    let {
        path = null,
    } = props;

    if (path === null) {
        path = AnonymousAvatarPath;
    }

    const componentProps = excludeProps(props, ['children', 'path']);
    return <div
        {...componentProps}
        className="Avatar--picture" style={{ backgroundImage: `url(${path})` }}>
        {props.children}
    </div>;
}

export default Avatar;
