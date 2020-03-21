import React from 'react';

import {
    buildParagraphSentenceMap,
    createParagraphSentenceMapKey,
} from '../../utilities/translations';
import {
    IProps,
    IState,
} from './TranslationForm._types';

export default class TranslationForm extends React.Component<IProps> {
    public static getDerivedStateFromProps(props: IProps, state: IState)
    {
        const {
            paragraphs,
            translations,
        } = props;

        if (paragraphs.length > 0 && translations.length > 0 && //
            paragraphs !== state.lastParagraphsRef) {
            console.log('updating state');
            let paragraphSentenceMap;
            try {
                paragraphSentenceMap = buildParagraphSentenceMap(paragraphs, translations);
            } catch (e) {
                // this is temporarily suppressed as older definitions actually do not have this structure
                // configured by default.
                paragraphSentenceMap = new Map();
                console.warn(e);
            }

            return {
                lastParagraphsRef: paragraphs, // save the reference
                paragraphSentenceMap,
            };
        }

        return null;
    }

    public state = {
        lastParagraphsRef: null,
        paragraphSentenceMap: new Map(),
    } as IState;

    public render() {
        const {
            translations,
        } = this.props;

        const {
            paragraphSentenceMap,
        } = this.state;

        return <>
            <p>This is optional but it enhances the experience with an English translation.</p>
            <dl>
                {translations.map((p, i) => <React.Fragment key={i}>
                    <dt>{paragraphSentenceMap.get(createParagraphSentenceMapKey(p.paragraphNumber, p.sentenceNumber))}</dt>
                    <dd>{JSON.stringify(translations)}</dd>
                </React.Fragment>)}
            </dl>
        </>;
    }
}
