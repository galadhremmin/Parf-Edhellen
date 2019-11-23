/// <reference path="../_types/html-to-react.d.ts" />
import {
    INode,
    Parser as HtmlToReactParser,
    ParserInstructions,
    ProcessNodeDefinitions,
} from 'html-to-react';
import React, {
    PureComponent,
    Suspense,
} from 'react';

import { isEmptyString } from '@root/utilities/func/string-manipulation';
import {
    fireEvent,
} from './Component';
import {
    IProps,
    IState,
} from './HtmlInject._types';

export default class HtmlInject extends PureComponent<IProps, IState> {
    public static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.html !== prevState.lastHtml) {
            return {
                lastHtml: null,
            } as IState;
        }

        return null;
    }

    public state: IState = {
        elements: null,
        lastHtml: null,
    };

    public componentDidMount() {
        this._inject();
    }

    public componentDidUpdate() {
        if (this.state.lastHtml === null) {
            this._inject();
        }
    }

    public render() {
        return this.state.elements;
    }

    private _inject() {
        const html = this.props.html;
        const elements = this._convertHtmlToReact(html);

        this.setState({
            elements,
            lastHtml: html,
        });
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
            // Special behavior for transcription nodes
            {
                processNode: this._transcriptionRender.bind(this, definitions),
                shouldProcessNode: (node: INode) => node.name === 'span',
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

    private _transcriptionRender(definitions: ProcessNodeDefinitions, node: INode, children: INode[]) {
        const transcribe = node.attribs['data-tengwar-transcribe'] === 'true';
        const mode = node.attribs['data-tengwar-mode'] as string;
        if (! transcribe || isEmptyString(mode)) {
            return definitions.processDefaultNode(node, children);
        }

        const Component = React.lazy(() => import('./Tengwar'));

        return <Suspense fallback={<span>&#128220;</span>}>
            <Component text={node.children[0].data} transcribe={true} mode={mode.toLowerCase()} />
        </Suspense>;
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
