import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProfileProps {
    introduction: string;
    nickname: string;
    tengwar: string;
}

export interface IProps extends IProfileProps {
    onIntroductionChange: ComponentEventHandler<string>;
    onNicknameChange: ComponentEventHandler<string>;
    onTengwarChange: ComponentEventHandler<string>;
    onSubmit: ComponentEventHandler<void>;
}
