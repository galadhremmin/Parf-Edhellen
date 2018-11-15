import React from 'react';

import {
    ParagraphState,
    IFragmentInSentenceState,
} from '../reducers/FragmentsReducer._types';
import {
    IProps,
} from './TextInspectorView._types';

export default class TextInspectorView extends React.PureComponent<IProps> {
    public render() {
        const {
            texts,
        } = this.props;

        if (!Array.isArray(texts)) {
            return null;
        }

        const text = [];
        let paragraphNumber = 0;
        while (true) {
            const paragraphs = texts.map(
                    (text, i) => this._renderParagraph(text.paragraphs[paragraphNumber], i, text.transformerName)
                )
                .filter((paragraph) => paragraph != null);

            if (paragraphs.length === 0) {
                break;
            }

            text.push(paragraphs);

            paragraphNumber += 1;
        }

        return text;
    }

    private _renderParagraph(paragraph: ParagraphState, key: number, transformerName: string) {
        if (paragraph === undefined) {
            return null;
        }

        return <div key={key} className={`paragraph paragraph--${transformerName}`}>
            {paragraph.map((fragment, i) => this._renderFragment(fragment, i))}
        </div>;
    }

    private _renderFragment(fragment: IFragmentInSentenceState, key: number) {
        return <span key={key}>{fragment.fragment}</span>;
    }
}
