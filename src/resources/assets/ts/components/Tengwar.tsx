import React from 'react';

import { DI, resolve } from '@root/di';
import {
    IProps,
    IState,
} from './Tengwar._types';

import './Tengwar.scss';

export default class Tengwar extends React.Component<IProps> {
    public static defaultProps = {
        as: 'span',
        transcriber: resolve(DI.Glaemscribe),
    } as Partial<IProps>;

    public static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.text !== prevState.lastText) {
            return {
                modeName: '',
                lastText: nextProps.text,
                transcribed: null,
            } as IState;
        }

        return null;
    }

    public state: IState = {
        lastText: null,
        transcribed: null,
    };

    public componentDidMount() {
        if (this.props.transcribe) {
            this._transcribe();
        }
    }

    public componentDidUpdate() {
        const {
            lastText,
            transcribed,
        } = this.state;

        if (this.props.transcribe && transcribed === null && lastText !== null) {
            this._transcribe();
        }
    }

    public render() {
        const className = 'tengwar';
        const Component = this.props.as as any;
        const {
            transcribe,
            text,
        } = this.props;
        const {
            modeName,
            transcribed,
        } = this.state;

        let tengwar = text;
        let title = '';
        if (transcribe) {
            tengwar = transcribed;
            title = `${text} (${modeName})`;
        }
        if (!tengwar) {
            return null;
        }

        return <Component className={className} title={title}>{tengwar}</Component>;
    }

    private async _transcribe() {
        const {
            mode,
            transcriber,
        } = this.props;
        const {
            lastText,
        } = this.state;

        const transcribed = await transcriber.transcribe(lastText, mode);
        const modeName = await transcriber.getModeName(mode);
        this.setState({
            modeName,
            transcribed,
        });
    }
}
