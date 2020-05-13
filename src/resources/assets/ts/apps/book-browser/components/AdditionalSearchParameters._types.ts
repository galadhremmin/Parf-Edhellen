import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    glossGroupId?: number;
    onGlossGroupIdChange: ComponentEventHandler<number>;
    onSpeechIdChange: ComponentEventHandler<number>;
    speechId?: number;
}
