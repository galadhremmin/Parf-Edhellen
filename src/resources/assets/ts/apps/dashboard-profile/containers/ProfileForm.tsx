import {
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
import TextIcon from '@root/components/TextIcon';
import FeatureBackgroundDialog from '../components/FeatureBackgroundDialog';
import Jumbotron from '@root/components/Jumbotron';
import Tengwar from '@root/components/Tengwar';

const ProfileForm = (props: IProps) => {
    const {
        account,
        api,
    } = props;
    const accountId = account.id;

    const [ avatarPath, setAvatarPath ] = useState(() => account.avatarPath || AnonymousAvatarPath);
    const [ featureBackground, setFeatureBackground ] = useState(account.featureBackgroundFile || null);
    const [ introduction, setIntroduction ] = useState(() => account.profile || '');
    const [ nickname, setNickname ] = useState(() => account.nickname || '');
    const [ tengwar, setTengwar ] = useState(() => account.tengwar || '');

    const [ errors, setErrors ] = useState(null);
    const [ openFeatureBackground, setOpenFeatureBackground ] = useState(false);

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

    const _onIntroductionChange = (ev: IComponentEvent<string>) => {
        setIntroduction(ev.value);
    };

    const _onNicknameChange = (ev: IComponentEvent<string>) => {
        setNickname(ev.value);
    };

    const _onTengwarChange = (ev: IComponentEvent<string>) => {
        setTengwar(ev.value);
    };

    const _onSelectBackground = async (ev: IComponentEvent<string>) => {
        try {
            const response = await api.saveFeatureBackground({
                accountId,
                featureBackgroundFile: ev.value,
            });

            setFeatureBackground(response.featureBackgroundFile);
        } catch (e) {
            setErrors(e);
        } finally {
            setOpenFeatureBackground(false);
        }
    };

    const _onDismissBackground = () => {
        _onSelectBackground({ value: null });
    }

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
        <Jumbotron className="InformationForm--avatar-form" backgroundImageUrl={featureBackground}>
            <button className="btn btn-secondary float-end" onClick={() => setOpenFeatureBackground(true)}>
                <TextIcon icon="edit" />
            </button>
            <AvatarForm path={avatarPath}
                        onAvatarChange={_onAvatarChange}
            />
            <h1>{nickname}</h1>
            {tengwar && <Tengwar as="h2" text={tengwar} />}
        </Jumbotron>
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
        <FeatureBackgroundDialog open={openFeatureBackground}
            accountApi={api}
            onSelectBackground={_onSelectBackground}
            onDismiss={_onDismissBackground}
        />
    </>;
};

ProfileForm.defaultProps = {
    api: resolve(DI.AccountApi),
};

export default ProfileForm;
