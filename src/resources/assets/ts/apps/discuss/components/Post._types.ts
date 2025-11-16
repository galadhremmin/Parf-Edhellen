import type { ReactNode } from 'react';
import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import type { IPostEntity } from '@root/connectors/backend/IDiscussApi';

export interface IProps {
    highlightThreadPost?: boolean;
    onReferenceLinkClick?: ComponentEventHandler<IReferenceLinkClickDetails>;
    post: IPostEntity;
    renderToolbar?: (props: IProps) => ReactNode;
}
