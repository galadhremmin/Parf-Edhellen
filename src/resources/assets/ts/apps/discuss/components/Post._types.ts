import { IPostEntity } from '@root/connectors/backend/IDiscussApi';

export interface IProps {
    post: IPostEntity;
    renderToolbar?: (props: IProps) => React.ReactNode;
}
