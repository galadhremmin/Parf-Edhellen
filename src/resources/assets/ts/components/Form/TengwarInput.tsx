import classNames from 'classnames';
import React, { useCallback } from 'react';

import LanguageConnector from '@root/connectors/backend/LanguageConnector';
import { excludeProps } from '@root/utilities/func/props';
import { isEmptyString } from '@root/utilities/func/string-manipulation';
import Glaemscribe from '@root/utilities/Glaemscribe';
import SharedReference from '@root/utilities/SharedReference';
import { fireEvent, fireEventAsync } from '../Component';
import { IProps } from './TengwarInput._types';

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

    const componentProps = excludeProps(props, [
        'className', 'inputSize', 'languageId', 'onChange',
        'originalText',
    ]);
    const componentClassName = classNames('form-control', 'tengwar', className || '');

    const _onChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        const { value: newValue } = ev.target;
        fireEvent(name, onChange, newValue);
    }, [ name, onChange, value ]);

    const _onTranscribe = useCallback(async () => {
        const languageConnector = SharedReference.getInstance(LanguageConnector);
        const language = await languageConnector.find(languageId);
        if (language === null) {
            alert('You must select a language first.');
            return;
        }

        if (language.tengwarMode === null) {
            alert(`We do not currently support transcription for ${language.name}.`);
            return;
        }

        const transcriber = SharedReference.getInstance(Glaemscribe);
        const transcribedText = await transcriber.transcribe(originalText, language.tengwarMode);

        if (transcribedText === undefined) {
            alert(`Transcription of "${originalText}" failed.`);
            return;
        }
        fireEventAsync(name, onChange, transcribedText);
    }, [ languageId, name, onChange, originalText ]);

    return <div className={`input-group input-group-${inputSize}`}>
        <input type="text"
            {...componentProps}
            className={componentClassName}
            onChange={_onChange}
        />
        <span className="input-group-btn">
            <button className="btn btn-default"
                    type="button"
                    onClick={_onTranscribe}
                    disabled={isEmptyString(originalText)}>
                Transcribe
            </button>
      </span>
    </div>;
}

TengwarInput.defaultProps = {
    inputSize: '',
    languageId: null,
    originalText: null,
    value: '',
} as IProps;

export default TengwarInput;
