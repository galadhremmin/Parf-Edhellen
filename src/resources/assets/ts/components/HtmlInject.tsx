import {
    INode,
    Parser as HtmlToReactParser,
    ParserInstructions,
    ProcessNodeDefinitions,
} from 'html-to-react';
import React, { PureComponent } from 'react';

import {
    IProps,
    IState,
} from './HtmlInject._types';

export default class Markdown extends PureComponent<IProps, IState> {
    public render() {
        return this._convertHtmlToReact(this.props.html) || null;
    }

    private _convertHtmlToReact(html: string) {
        if (! html || /^\s*$/.test(html)) {
            return null;
        }

        const definitions = new ProcessNodeDefinitions(React);
        const instructions: ParserInstructions = [
            // Special behaviour for <a> as they are reference links.
            {
                shouldProcessNode: (node) => node.name === 'a',
                processNode: this._referenceRender.bind(this, definitions)
            },
            // Default behaviour for all else.
            {
                shouldProcessNode: (node) => true,
                processNode: definitions.processDefaultNode
            }];

        const parser = new HtmlToReactParser();
        return parser.parseWithInstructions(html, n => true, instructions);
    }

    private _referenceRender(definitions: ProcessNodeDefinitions, node: INode, children: INode[]) {
        const nodeElements = definitions.processDefaultNode(node, children);
        if (node.attribs.class !== 'ed-word-reference') {
            return nodeElements;
        }

        // Replace reference links with a link that is aware of
        // the component, and can intercept click attempts.
        const href = node.attribs.href;
        const title = node.attribs.title;
        const word = node.attribs['data-word'];
        const languageShortName = node.attribs['data-language-short-name'];
        const childElements = nodeElements.props.children;

        return <a href={href} onClick={this._onReferenceLinkClick.bind(this, word, languageShortName)}
            title={title}>{childElements}</a>;
    }

    private _onReferenceLinkClick(word: string, languageShortName: string, ev: React.MouseEvent<HTMLAnchorElement>) {
        ev.preventDefault();

        if (typeof this.props.onReferenceLinkClick === 'function') {
            this.props.onReferenceLinkClick({
                name: null,
                value: {
                    languageShortName,
                    word,
                },
            });
        }
    }
}