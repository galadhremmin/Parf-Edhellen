import {
    useCallback,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import { AnonymousAvatarPath } from '@root/config';
import { withPropResolving } from '@root/di';
import { DI } from '@root/di/keys';

import AvatarForm from '../components/AvatarForm';
import InformationForm from '../components/InformationForm';
import { IProps } from './ProfileForm._types';

import Jumbotron from '@root/components/Jumbotron';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import FeatureBackgroundDialog from '../components/FeatureBackgroundDialog';

import './ProfileForm.scss';

const ProfileForm = (props: IProps) => {
    const {
        account,
        api: accountApi,
    } = props;
    const accountId = account.id;

    const [ avatarPath, setAvatarPath ] = useState(() => account.avatarPath || AnonymousAvatarPath);
    const [ featureBackground, setFeatureBackground ] = useState(account.featureBackgroundUrl || null);
    const [ introduction, setIntroduction ] = useState(() => account.profile || '');
    const [ nickname, setNickname ] = useState(() => account.nickname || '');
    const [ tengwar, setTengwar ] = useState(() => account.tengwar || '');

    const [ errors, setErrors ] = useState(null);
    const [ openFeatureBackground, setOpenFeatureBackground ] = useState(false);

    const _onAvatarChange = useCallback(async (ev: IComponentEvent<File>) => {
        try {
            const response = await accountApi.saveAvatar({
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
    }, [ accountId, avatarPath ]);

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
            const response = await accountApi.saveFeatureBackground({
                accountId,
                featureBackgroundUrl: ev.value,
            });

            setFeatureBackground(response.featureBackgroundUrl);
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
            const response = await accountApi.saveProfile({
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
    }, [ accountId, introduction, nickname, tengwar ]);

    return <>
        <ValidationErrorAlert error={errors} />
        <Jumbotron className="InformationForm--avatar-form" backgroundImageUrl={featureBackground}>
            <AvatarForm path={avatarPath}
                        onAvatarChange={_onAvatarChange}
            />
            <h1>{nickname}</h1>
            {tengwar && <Tengwar as="h2" text={tengwar} />}
            <button className="btn btn-secondary" onClick={() => setOpenFeatureBackground(true)}>
                <TextIcon icon="edit" />{' '}
                Change background
            </button>
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
            accountApi={accountApi}
            onSelectBackground={_onSelectBackground}
            onDismiss={_onDismissBackground}
        />
    </>;
};

export default withPropResolving(ProfileForm, {
    api: DI.AccountApi,
});
