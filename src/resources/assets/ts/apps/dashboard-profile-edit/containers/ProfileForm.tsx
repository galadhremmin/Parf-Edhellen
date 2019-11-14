import React, {
    useCallback,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import InformationForm from '../components/InformationForm';
import { IProps } from './ProfileForm._types';

const ProfileForm = (props: IProps) => {
    const {
        account,
    } = props;

    const [ introduction, setIntroduction ] = useState(() => account.profile || '');
    const [ nickname, setNickname ] = useState(() => account.nickname || '');
    const [ tengwar, setTengwar ] = useState(() => account.tengwar || '');

    const _onIntroductionChange = useCallback((ev: IComponentEvent<string>) => {
        setIntroduction(ev.value);
    }, [ setIntroduction ]);

    const _onNicknameChange = useCallback((ev: IComponentEvent<string>) => {
        setNickname(ev.value);
    }, [ setNickname ]);

    const _onTengwarChange = useCallback((ev: IComponentEvent<string>) => {
        setTengwar(ev.value);
    }, [ setTengwar ]);

    return <>
        <InformationForm introduction={introduction}
                         nickname={nickname}
                         tengwar={tengwar}
                         onIntroductionChange={_onIntroductionChange}
                         onNicknameChange={_onNicknameChange}
                         onTengwarChange={_onTengwarChange}
        />
    </>;
};

export default ProfileForm;
