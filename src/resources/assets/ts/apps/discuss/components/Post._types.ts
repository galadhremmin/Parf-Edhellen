import { IPostEntity } from '@root/connectors/backend/DiscussApiConnector._types';

export interface IProps {
    post: IPostEntity;
    renderToolbar?: (props: IProps) => React.ReactNode;
}
