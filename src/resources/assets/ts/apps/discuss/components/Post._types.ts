import { ComponentEventHandler } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { IPostEntity } from '@root/connectors/backend/IDiscussApi';

export interface IProps {
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
    post: IPostEntity;
    renderToolbar?: (props: IProps) => React.ReactNode;
}
