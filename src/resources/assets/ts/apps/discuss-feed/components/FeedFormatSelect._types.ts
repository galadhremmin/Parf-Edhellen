import {
    ComponentEventHandler,
    IComponentProps,
} from '@root/components/Component._types';
import { FeedFormat } from '@root/connectors/FeedApiConnector._types';

export interface IProps extends IComponentProps {
    onChange: ComponentEventHandler<FeedFormat>;
    value: FeedFormat;
}
