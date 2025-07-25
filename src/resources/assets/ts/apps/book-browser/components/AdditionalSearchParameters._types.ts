import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    lexicalEntryGroupId?: number;
    onLexicalEntryGroupIdChange: ComponentEventHandler<number>;
    onSpeechIdChange: ComponentEventHandler<number>;
    speechId?: number;
}
