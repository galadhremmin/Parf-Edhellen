import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps {
    fragment?: ISentenceFragmentEntity;
    fragmentId: number;
    onNextFragmentClick?: ComponentEventHandler<number>;
    onPreviousFragmentClick?: ComponentEventHandler<number>;
}
