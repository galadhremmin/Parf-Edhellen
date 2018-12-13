import React from 'react';

import UtilityApiConnector from '../connectors/backend/UtilityApiConnector';
import SharedReference from '../utilities/SharedReference';
import {
    IProps,
    IState,
} from './Markdown._types';
import HtmlInject from './HtmlInject';

export default class Markdown extends React.PureComponent<IProps, IState> {
    public static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.parse && nextProps.text !== prevState.lastText) {
            return {
                html: null,
                lastText: nextProps.text,
            } as IState;
        }

        return null;
    }

    public state: IState = {
        html: null,
        lastText: null,
    };

    private _api = new SharedReference(UtilityApiConnector);

    public componentDidMount() {
        if (this.props.parse) {
            this._parse(this.props.text);
        }
    }

    public componentDidUpdate() {
        const {
            html,
            lastText,
        } = this.state;

        if (this.props.parse && html === null && lastText !== null) {
            this._parse(lastText);
        }
    }

    public render() {
        const {
            html,
        } = this.state;
        const {
            parse,
            text,
        } = this.props;

        return parse
            ? <HtmlInject html={html} />
            : text;
    }

    private async _parse(markdown: string) {
        let html: string;
        try {
            const response = await this._api.value.parseMarkdown({
                markdown,
            });

            html = response.html;
        } catch (ex) {
            html = markdown;
        }

        this.setState({
            html,
        });
    }
}
