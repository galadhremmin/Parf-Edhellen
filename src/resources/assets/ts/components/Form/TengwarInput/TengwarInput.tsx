import classNames from 'classnames';
import {
    useCallback,
    useState,
    lazy,
    type ChangeEvent,
} from 'react';

import { excludeProps } from '@root/utilities/func/props';
import {
    fireEvent, fireEventAsync,
} from '../../Component';
import type { IComponentEvent } from '../../Component._types';
import Dialog from '../../Dialog';
import type { IProps } from './TengwarInput._types';
import type { ITranscription } from './TranscriberForm._types';

const TranscriberFormAsync = lazy(() => import('./TranscriberForm'));

function TengwarInput(props: IProps) {
    const {
        className,
        inputSize = '',
        languageId = null,
        onChange,
        name,
        originalText = '',
        value = '',
    } = props;

    const [ newTranscription, setNewTranscription ] = useState(() => value);
    const [ isDialogOpen, setIsDialogOpen ] = useState(false);

    const componentProps = excludeProps(props, [
        'className', 'inputSize', 'languageId', 'onChange',
        'originalText',
    ]);
    const componentClassName = classNames('form-control', 'tengwar', className || '');

    const _onChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        const { value: newValue } = ev.target;
        void fireEvent(name, onChange, newValue);
    }, [ name, onChange, value ]);

    const _onTranscribe = useCallback(() => {
        setIsDialogOpen(true);
        setNewTranscription(value);
    }, [ value ]);

    const _onDismissDialog = useCallback(() => {
        setIsDialogOpen(false);
        setNewTranscription(null);
    }, []);

    const _onConfirmDialog = useCallback((ev: IComponentEvent<string>) => {
        setIsDialogOpen(false);
        void fireEventAsync(name, onChange, newTranscription);
    }, [ name, onChange, newTranscription ]);

    const _onTranscription = useCallback((ev: IComponentEvent<ITranscription>) => {
        setNewTranscription(ev.value.transcription);
    }, []);

    return <div className={`input-group input-group-${inputSize}`}>
        <input type="text"
            {...componentProps}
            className={componentClassName}
            onChange={_onChange}
        />
        <span className="input-group-btn">
            <button className="btn btn-secondary"
                    type="button"
                    onClick={_onTranscribe}>
                Transcribe
            </button>
        </span>
        <Dialog<string> cancelButtonText="Cancel"
                        confirmButtonText="Apply"
                        open={isDialogOpen}
                        size="lg"
                        title="Transcription"
                        onDismiss={_onDismissDialog}
                        onConfirm={_onConfirmDialog}
                        value={value}>
            {isDialogOpen && <TranscriberFormAsync languageId={languageId}
                text={originalText}
                transcription={newTranscription}
                onTranscription={_onTranscription} />}
        </Dialog>
    </div>;
}


export default TengwarInput;
