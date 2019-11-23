import React from 'react';

import Glaemscribe from '@root/utilities/Glaemscribe';
import SharedReference from '@root/utilities/SharedReference';
import {
    IProps,
    IState,
} from './Tengwar._types';

import './Tengwar.scss';

export default class Tengwar extends React.Component<IProps> {
    public static defaultProps = {
        as: 'span',
        transcriber: new SharedReference(Glaemscribe),
    };

    public static getDerivedStateFromProps(nextProps: IProps, prevState: IState) {
        if (nextProps.text !== prevState.lastText) {
            return {
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

        const tengwar = transcribe ? this.state.transcribed : text;
        if (!tengwar) {
            return null;
        }

        return <Component className={className}>{tengwar}</Component>;
    }

    private async _transcribe() {
        const transcribed = await this.props.transcriber.value.transcribe(this.state.lastText, this.props.mode);
        this.setState({
            transcribed,
        });
    }
}
