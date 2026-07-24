import { fireEvent } from '@root/components/Component';
import type { IProps } from './UnusualLanguagesWarning._types';

function UnusualLanguagesWarning({
    showOverrideOption,
    onOverrideOptionTriggered,
}: IProps) {
    return <div className="text-center mt-4">
        <h3>
            The entries below are from Tolkien's earlier conceptional periods
        </h3>
        <p>
            Tolkien likely changed these words as he evolved the aesthetics and completeness of the languages. You may even find
            languages that Tolkien later rejected. Avoid mixing words from different time periods.
        </p>
        {showOverrideOption && <>
            <p>
                You can view these words by clicking the button below. You will not be asked again (unless you clear your browser's local storage!)
            </p>
            <button className="btn btn-secondary" onClick={() => void fireEvent('UnusualLanguagesWarning', onOverrideOptionTriggered)}>
                I understand - show me the words!
            </button>
        </>}
    </div>;
}

export default UnusualLanguagesWarning;
