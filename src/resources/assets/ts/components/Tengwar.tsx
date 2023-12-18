import { withPropResolving } from '@root/di';
import { DI } from '@root/di/keys';
import { IProps } from './Tengwar._types';

import { useEffect, useState } from 'react';
import './Tengwar.scss';

function Tengwar(props: IProps) {
    const {
        as: Component,
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

export default withPropResolving(Tengwar, {
    transcriber: DI.Glaemscribe,
});
