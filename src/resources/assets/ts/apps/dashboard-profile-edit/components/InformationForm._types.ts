import { ComponentEventHandler } from '@root/components/Component._types';
import { IAccountEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps {
    introduction: string;
    nickname: string;
    tengwar: string;

    onIntroductionChange: ComponentEventHandler<string>;
    onNicknameChange: ComponentEventHandler<string>;
    onTengwarChange: ComponentEventHandler<string>;
}
