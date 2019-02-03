import React from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import MarkdownInput from '@root/components/MarkdownInput';

import {
    IProps,
    IState,
} from './Form._types';

export default class Form extends React.PureComponent<IProps, IState> {
    public static defaultProps = {
        subjectEnabled: true,
    } as Partial<IProps>;

    public state = {
        content: '',
        subject: '',
    } as IState;

    public componentDidMount() {
        const {
            content,
            subject,
        } = this.props;

        this.setState({
            content,
            subject,
        });
    }

    public render() {
        const {
            subjectEnabled,
        } = this.props;

        const {
            content,
            subject,
        } = this.state;

        return <form method="post" action="/dashboard/discuss">
            {subjectEnabled && <div className="form-group">
                <label htmlFor="ed-discuss-subject" className="control-label">Subject</label>
                <input type="text"
                       className="form-control"
                       id="ed-discuss-subject"
                       name="subject"
                       value={subject}
                       onChange={this._onSubjectChange}
                />
            </div>}
            <div className="form-group">
                <label htmlFor="ed-discuss-content" className="control-label">Message</label>
                <MarkdownInput id="ed-discuss-content"
                               className="form-control"
                               name="content"
                               rows={8}
                               value={content}
                               onChange={this._onContentChange}
                />
            </div>
            <div className="form-group text-right">
                <a href="/discuss" className="btn btn-default">Cancel</a>
                <button type="submit" className="btn btn-primary">
                    <span className="glyphicon glyphicon-pencil"></span>
                    Save
                </button>
            </div>
        </form>;
    }

    private _onSubjectChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        console.log(ev);
        this.setState({
            subject: ev.target.value,
        });
    }

    private _onContentChange = (ev: IComponentEvent<string>) => {
        console.log(ev);
        this.setState({
            content: ev.value,
        });
    }
}
