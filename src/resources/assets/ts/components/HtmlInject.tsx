/// <reference path="../_types/html-to-react.d.ts" />
import {
    INode,
    Parser as HtmlToReactParser,
    ParserInstructions,
    ProcessNodeDefinitions,
} from 'html-to-react';
import React, { PureComponent } from 'react';

import {
    fireEvent,
} from './Component';
import {
    IProps,
    IState,
} from './HtmlInject._types';

export default class HtmlInject extends PureComponent<IProps, IState> {
    public state: IState = {
        elements: null,
        lastHtml: null,
    };

    static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.html !== prevState.lastHtml) {
            return {
                lastHtml: null,
            } as IState;
        }

        return null;
    }

    public componentDidUpdate() {
        if (this.state.lastHtml === null) {
            const elements = this._convertHtmlToReact(this.props.html);

            this.setState({
                elements,
                lastHtml: this.props.html,
            });
        }
    }

    public render() {
        return this.state.elements;
    }

    private _convertHtmlToReact(html: string) {
        if (! html || /^\s*$/.test(html)) {
            return null;
        }

        const definitions = new ProcessNodeDefinitions(React);
        const instructions: ParserInstructions = [
            // Special behaviour for <a> as they are reference links.
            {
                processNode: this._referenceRender.bind(this, definitions),
                shouldProcessNode: (node: INode) => node.name === 'a',
            },
            // Default behaviour for all else.
            {
                processNode: definitions.processDefaultNode,
                shouldProcessNode: () => true,
            }];

        const parser = new HtmlToReactParser();
        return parser.parseWithInstructions(html, () => true, instructions);
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
        const normalizedWord = node.attribs['data-word'];
        const word = node.attribs['data-original-word'];
        const languageShortName = node.attribs['data-language-short-name'];
        const childElements = nodeElements.props.children;

        return <a href={href} onClick={this._onReferenceLinkClick.bind(this, word, normalizedWord, languageShortName)}
            title={title}>{childElements}</a>;
    }

    private _onReferenceLinkClick(word: string, normalizedWord: string, languageShortName: string,
        ev: React.MouseEvent<HTMLAnchorElement>) {
        ev.preventDefault();

        fireEvent(this, this.props.onReferenceLinkClick, {
            languageShortName,
            normalizedWord,
            word,
        });
    }
}
