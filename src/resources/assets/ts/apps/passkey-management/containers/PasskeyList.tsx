import PasskeyListItem from './PasskeyListItem';
import type { IProps } from './PasskeyList._types';

const PasskeyList = (props: IProps) => {
    const { passkeys, onPasskeyDeleted, passkeyApi } = props;

    if (passkeys.length === 0) {
        return (
            <div className="PasskeyList PasskeyList--empty">
                <p>You haven't added any passkeys yet. Add one to enhance your account security.</p>
            </div>
        );
    }

    return (
        <div className="PasskeyList">
            <div className="PasskeyList__items">
                {passkeys.map((passkey) => (
                    <PasskeyListItem
                        key={passkey.id}
                        passkey={passkey}
                        onDeleted={onPasskeyDeleted}
                        passkeyApi={passkeyApi}
                    />
                ))}
            </div>
        </div>
    );
};

export default PasskeyList;
