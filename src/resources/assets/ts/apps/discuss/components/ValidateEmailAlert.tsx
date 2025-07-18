import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';

function ValidateEmailAlert() {
    return <StaticAlert type="info">
        <strong>
            <TextIcon icon="info-sign" />
            {' '}
            Please validate your email address to participate in discussions.
        </strong>
        {' '}
        We've had to disable posting for accounts with an unverified email address because of spam.{' '}
        Fortunately, it's easy to fix: just click the link in the email we sent you when you registered.
        <div className="mt-2 text-center">
            <button
                className="btn btn-primary"
                onClick={() => window.location.href = '/account/verification-required'}
            >
                Didn't receive the email?
            </button>
        </div>
    </StaticAlert>;
}

export default ValidateEmailAlert;
