import { useState, useCallback } from 'react';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import registerApp from '../app';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';
import PasskeyLoginButton from './containers/PasskeyLoginButton';

interface IProps {
    passkeyApi?: IPasskeyApi;
}

const PasskeyLogin = (props: IProps) => {
    const { passkeyApi } = props;

    return <PasskeyLoginButton passkeyApi={passkeyApi} />;
};

const PasskeyLoginWithDI = withPropInjection(PasskeyLogin, {
    passkeyApi: DI.PasskeyApi,
});

export default registerApp(PasskeyLoginWithDI);
