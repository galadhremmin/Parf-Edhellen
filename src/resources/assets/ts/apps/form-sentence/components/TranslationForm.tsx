import React from 'react';

import { IProps } from './TranslationForm._types';

function TranslationForm(props: IProps) {
    const {
        translations,
        paragraphs,
    } = props;

    return <>
        <p>This is optional but it enhances the experience with an English translation.</p>
        <dl>
            {translations.map((t) => <React.Fragment key={`${t.paragraphNumber}.${t.sentenceNumber}`}>
                <dt>{JSON.stringify(paragraphs)}</dt>
                <dd>{JSON.stringify(t)}</dd>
            </React.Fragment>)}
        </dl>
    </>;
}

export default TranslationForm;
