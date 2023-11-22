import { IProps } from './ProfileLink._types';

const ProfileLink = (props: IProps) => {
    const {
        account,
    } = props;

    const children = props.children || props.account?.nickname || null;

    if (account !== null && typeof account === 'object') {
        return <a href={`/author/${props.account.id}`}
            title={`View ${props.account.nickname}'s profile`}
            className={props.className}>
            {children}
        </a>;
    } else {
        // No account = no link. Render nothing.
        return children;
    }
};

export default ProfileLink;
