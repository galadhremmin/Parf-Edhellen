import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';

function ValidateEmailAlert() {
    return <StaticAlert type="info">
        <strong>
            <TextIcon icon="info-sign" />
            {' '}
            Confirm your e-mail address to join the conversation.
        </strong>
        {' '}
        Posting is only open to confirmed addresses, which keeps spam out. It takes a minute: we'll e-mail you a
        code to enter.
        <div className="mt-2 text-center">
            <button
                className="btn btn-primary"
                onClick={() => window.location.href = '/account/verification-required'}
            >
                Confirm my e-mail address
            </button>
        </div>
    </StaticAlert>;
}

export default ValidateEmailAlert;
