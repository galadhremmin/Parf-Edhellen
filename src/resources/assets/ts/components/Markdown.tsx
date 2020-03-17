import React from 'react';

import IUtilityApi from '@root/connectors/backend/IUtilityApi';
import { DI, resolve } from '@root/di';
import { isEmptyString } from '@root/utilities/func/string-manipulation';

import HtmlInject from './HtmlInject';
import {
    IProps,
    IState,
} from './Markdown._types';

export default class Markdown extends React.Component<IProps, IState> {
    public static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.parse && nextProps.text !== prevState.lastText) {
            return {
                dirty: true,
                lastText: nextProps.text,
            } as IState;
        }

        return null;
    }

    public state: IState = {
        dirty: true,
        html: null,
        lastText: null,
    };

    private _api = resolve<IUtilityApi>(DI.UtilityApi);

    public componentDidMount() {
        if (this.props.parse) {
            this._parse(this.props.text);
        }
    }

    public componentDidUpdate() {
        const {
            dirty,
            lastText,
        } = this.state;

        if (this.props.parse && dirty) {
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
        let html = markdown;
        if (! isEmptyString(markdown)) {
            try {
                const response = await this._api.parseMarkdown({
                    markdown,
                });

                html = response.html;
            } catch (ex) {
                html = markdown;
            }
        }

        this.setState({
            dirty: false,
            html,
        });
    }
}
