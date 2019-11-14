import classNames from 'classnames';
import React, {
    useCallback,
    useState,
} from 'react';

import { excludeProps } from '@root/utilities/func/props';
import {
    fireEvent, fireEventAsync,
} from '../../Component';
import { IComponentEvent } from '../../Component._types';
import Dialog from '../../Dialog';
import { IProps } from './TengwarInput._types';
import { ITranscription } from './TranscriberForm._types';

const TranscriberFormAsync = React.lazy(() => import('./TranscriberForm'));

function TengwarInput(props: IProps) {
    const {
        className,
        inputSize,
        languageId,
        onChange,
        name,
        originalText,
        value,
    } = props;

    const [ newTranscription, setNewTranscription ] = useState(() => value);
    const [ isDialogOpen, setIsDialogOpen ] = useState(false);

    const componentProps = excludeProps(props, [
        'className', 'inputSize', 'languageId', 'onChange',
        'originalText',
    ]);
    const componentClassName = classNames('form-control', 'tengwar', className || '');

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        const { value: newValue } = ev.target;
        fireEvent(name, onChange, newValue);
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
        fireEventAsync(name, onChange, newTranscription);
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
            <button className="btn btn-default"
                    type="button"
                    onClick={_onTranscribe}>
                Transcribe
            </button>
        </span>
        <Dialog<string> cancelButtonText="Cancel"
                        confirmButtonText="Apply"
                        open={isDialogOpen}
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

TengwarInput.defaultProps = {
    inputSize: '',
    languageId: null,
    originalText: null,
    value: '',
} as IProps;

export default TengwarInput;
