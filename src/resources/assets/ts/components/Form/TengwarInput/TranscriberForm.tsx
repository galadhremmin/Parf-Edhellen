import React, {
    useCallback,
    useEffect,
    useState,
} from 'react';

import { fireEventAsync } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Tengwar from '@root/components/Tengwar';

import LanguageSelect, {
    LanguageAndWritingModeFormatter,
    LanguageWithWritingModeOnlyFilter,
} from '../LanguageSelect';
import { transcribe } from './transcriber';
import { IProps } from './TranscriberForm._types';

import './TranscriberForm.scss';
import StaticAlert from '@root/components/StaticAlert';

function TranscriberForm(props: IProps) {
    const {
        languageId,
        text,
        transcription,
        onTranscription,
    } = props;

    const [ subjectLanguageId, setSubjectLanguageId ] = useState(() => languageId || 0);
    const [ textSubject, setTextSubject ] = useState(() => text || '');
    const [ error, setError ] = useState<string>(null);

    const updateTranscription = (newText: string, newLanguageId: number) => {
        transcribe(newText, newLanguageId).then((newTranscription) => {
            fireEventAsync('TranscriberForm', onTranscription, {
                text: newText,
                transcription: newTranscription,
            });
            setError(null);
        }).catch ((reason) => {
            setError(String(reason));
        });
    };

    const _onTextChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        const newText = ev.target.value || '';
        setTextSubject(newText);
        updateTranscription(newText, subjectLanguageId);
    }, [ subjectLanguageId ]);

    const _onLanguageChange = useCallback((ev: IComponentEvent<number>) => {
        setSubjectLanguageId(ev.value);
        updateTranscription(textSubject, ev.value);
    }, [ textSubject ]);

    useEffect(() => {
        // If the transcription is null and the language is valid, pre-transcribe for the client :)
        if (! transcription && !! subjectLanguageId) {
            updateTranscription(textSubject, subjectLanguageId);
        }
    }, [ textSubject, subjectLanguageId ]);

    return <div className="TranscriberForm">
        <fieldset className="TranscriberForm__language">
            <legend>Language</legend>
            <p>Select the language and the corresponding writing mode for the transcription.</p>
            <LanguageSelect className="form-control"
                            filter={LanguageWithWritingModeOnlyFilter}
                            formatter={LanguageAndWritingModeFormatter}
                            includeAllLanguages={false}
                            value={subjectLanguageId}
                            onChange={_onLanguageChange} />
        </fieldset>
        <fieldset className="TranscriberForm__text">
            <legend>Text</legend>
            <p>Write the text you would like to transcribe.</p>
            <input type="text"
                value={textSubject}
                onChange={_onTextChange}
                className="form-control" />
        </fieldset>
        {error && <StaticAlert type="danger">Transcription failed! {error}</StaticAlert>}
        <fieldset className="TranscriberForm__transcription">
            <legend>Transcription</legend>
            {transcription ? <Tengwar as="div" text={transcription} transcribe={false} />
                           : <em>The transcription will be presented here.</em>}
        </fieldset>
    </div>;
}

export default TranscriberForm;
