import Dialog from './Dialog';

import { IProps } from './AuthenticationDialog._types';

const _onConfirm = () => {
    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
};

function AuthenticationDialog(props: IProps) {
    const {
        featureName,
        onDismiss,
        open,
    } = props;

    return <Dialog title="Thank you, but you need an account to do that!"
        cancelButtonText="Continue without logging in"
        confirmButtonText="Create account or sign in"
        onConfirm={_onConfirm} onDismiss={onDismiss} open={open}>
        <p>
            {featureName ? `To ${featureName}` : 'To use this feature'} you need an account.
            If you already have one, all you need to do is to sign in.
        </p>
        <p>
            Creating an account is free and easy! With an account, you can contribute to the dictionary
            and phrase book, participate in discussions, save and review flashcard results, and much more.
        </p>
        <p>
            We will not save any personal information about you (except your e-mail address). You
            do not even need a password, as we trust third-party identity providers (like Google
            and Facebook) to vouch for you.
        </p>
    </Dialog>;
}

export default AuthenticationDialog;
