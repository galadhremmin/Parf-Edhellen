import React from 'react';

import {
    ParagraphState,
} from '../reducers/FragmentsReducer._types';
import {
    IProps,
} from './TextInspectorView._types';

import Fragment from './Fragment';
import Paragraph from './Paragraph';

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
        return <Paragraph transformerName={transformerName} key={key}>
            {paragraph.map((fragment, i) => <Fragment fragment={fragment} key={i} />)}
        </Paragraph>;
    }
}
