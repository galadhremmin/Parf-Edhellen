import React, {
    useCallback,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import { AnonymousAvatarPath } from '@root/config';
import { DI, resolve } from '@root/di';

import AvatarForm from '../components/AvatarForm';
import InformationForm from '../components/InformationForm';
import { IProps } from './ProfileForm._types';

import './ProfileForm.scss';

const ProfileForm = (props: IProps) => {
    const {
        account,
        api,
    } = props;
    const accountId = account.id;

    const [ avatarPath, setAvatarPath ] = useState(() => account.avatarPath || AnonymousAvatarPath);
    const [ introduction, setIntroduction ] = useState(() => account.profile || '');
    const [ nickname, setNickname ] = useState(() => account.nickname || '');
    const [ tengwar, setTengwar ] = useState(() => account.tengwar || '');

    const [ errors, setErrors ] = useState(null);

    const _onAvatarChange = useCallback(async (ev: IComponentEvent<File>) => {
        try {
            const response = await api.saveAvatar({
                accountId,
                file: ev.value,
            });

            setAvatarPath(avatarPath === response.avatarPath //
                ? URL.createObjectURL(ev.value) // force a refresh of the avatar image
                : response.avatarPath,
            );

            setErrors(null);
        } catch (e) {
            setErrors(e);
        }
    }, [ accountId, avatarPath, api ]);

    const _onIntroductionChange = useCallback((ev: IComponentEvent<string>) => {
        setIntroduction(ev.value);
    }, [ setIntroduction ]);

    const _onNicknameChange = useCallback((ev: IComponentEvent<string>) => {
        setNickname(ev.value);
    }, [ setNickname ]);

    const _onTengwarChange = useCallback((ev: IComponentEvent<string>) => {
        setTengwar(ev.value);
    }, [ setTengwar ]);

    const _onSubmit = useCallback(async () => {
        try {
            const response = await api.saveProfile({
                accountId,
                introduction,
                nickname,
                tengwar,
            });

            setErrors(null);
            window.location.href = response.profileUrl;
        } catch (e) {
            setErrors(e);
        }
    }, [ accountId, api, introduction, nickname, tengwar ]);

    return <>
        <ValidationErrorAlert error={errors} />
        <section className="InformationForm--avatar-form">
            <AvatarForm path={avatarPath}
                        onAvatarChange={_onAvatarChange}
            />
            <div className="InformationForm--avatar-form__instructions">
                Click or drag to change.
            </div>
        </section>
        <section className="InformationForm--info-form">
            <InformationForm introduction={introduction}
                            nickname={nickname}
                            tengwar={tengwar}
                            onIntroductionChange={_onIntroductionChange}
                            onNicknameChange={_onNicknameChange}
                            onTengwarChange={_onTengwarChange}
                            onSubmit={_onSubmit}
            />
        </section>
    </>;
};

ProfileForm.defaultProps = {
    api: resolve(DI.AccountApi),
};

export default ProfileForm;
