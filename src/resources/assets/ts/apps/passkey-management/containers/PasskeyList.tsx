import PasskeyListItem from './PasskeyListItem';
import type { IProps } from './PasskeyList._types';

const PasskeyList = (props: IProps) => {
    const { passkeys, onPasskeyDeleted, passkeyApi } = props;

    // The empty state is the panel's footer note, next to the button that fills it.
    if (passkeys.length === 0) {
        return null;
    }

    return (
        <ul className="ed-list PasskeyList">
            {passkeys.map((passkey) => (
                <PasskeyListItem
                    key={passkey.id}
                    passkey={passkey}
                    onDeleted={onPasskeyDeleted}
                    passkeyApi={passkeyApi}
                />
            ))}
        </ul>
    );
};

export default PasskeyList;
