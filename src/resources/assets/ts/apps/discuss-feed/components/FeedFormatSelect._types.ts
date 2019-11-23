import {
    ComponentEventHandler,
    IComponentProps,
} from '@root/components/Component._types';
import { FeedFormat } from '@root/connectors/IFeedApi';

export interface IProps extends IComponentProps {
    onChange: ComponentEventHandler<FeedFormat>;
    value: FeedFormat;
}
