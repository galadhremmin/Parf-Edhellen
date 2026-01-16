import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import registerApp from '../app';
import PasskeyLoginButton from './containers/PasskeyLoginButton';
import type { IProps } from './index._types';

const PasskeyLogin = (props: IProps) => {
    const { passkeyApi } = props;

    return <PasskeyLoginButton passkeyApi={passkeyApi} />;
};

const PasskeyLoginWithDI = withPropInjection(PasskeyLogin, {
    passkeyApi: DI.PasskeyApi,
});

export default registerApp(PasskeyLoginWithDI);
