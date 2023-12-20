import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useState } from 'react';

import { IProps } from './Tengwar._types';

import './Tengwar.scss';

function Tengwar(props: IProps) {
    const {
        as: Component,
        globalEvents,
        mode,
        text,
        transcribe,
        transcriber,
    } = props;

    const [ modeName, setModeName ] = useState<string | null>(null);
    const [ transcribedText, setTranscribedText ] = useState<string | null>(null);

    useEffect(() => {
        if (transcribe) {
            Promise.all([
                transcriber.transcribe(text, mode),
                transcriber.getModeName(mode),
            ]).then(([ nextTranscribedText, nextModeName ]) => {
                setTranscribedText(nextTranscribedText);
                setModeName(nextModeName);
            }).catch((error) => {
                globalEvents.fire(globalEvents.errorLogger, error);
            });
        }
    }, [ text ]);

    const className = 'tengwar';
    let tengwar = text;
    let title = '';

    if (transcribe) {
        tengwar = transcribedText;
        title = `${text} (${modeName})`;
    }
    if (! tengwar) {
        return null;
    }

    return <Component className={className} title={title}>{tengwar}</Component>;
}

Tengwar.defaultProps = {
    as: 'span',
} as Partial<IProps>;

export default withPropInjection(Tengwar, {
    globalEvents: DI.GlobalEvents,
    transcriber: DI.Glaemscribe,
});
