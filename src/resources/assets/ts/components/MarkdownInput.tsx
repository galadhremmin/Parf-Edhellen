import classNames from 'classnames';
import React from 'react';

import { isEmptyString } from '@root/utilities/func/string-manipulation';
import Markdown from './Markdown';
import {
    IProps,
    IState,
    Tab,
} from './MarkdownInput._types';

export default class MarkdownInput extends React.PureComponent<IProps, IState> {
    public static defaultProps = {
        id: 'markdownBody',
        name: 'markdownBody',
        props: {},
        rows: 15,
        value: '',
    } as Partial<IProps>;

    public state = {
        currentTab: Tab.EditTab,
        value: '',
    };

    public componentDidMount() {
        const {
            value,
        } = this.props;

        this.setState({
            value,
        });
    }

    public render() {
        const {
            id,
            name,
            rows,
        } = this.props;

        const {
            currentTab,
            value,
        } = this.state;

        return <div className="clearfix">
            {this._renderTabs()}
            <div className={classNames({ hidden: currentTab !== Tab.EditTab })}>
                <textarea className="form-control"
                          name={name}
                          id={id}
                          rows={rows}
                          value={value}
                          onChange={this._onMarkdownChange}
                />
                <small className="pull-right">
                    {' Supports Markdown. '}
                    <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                        Read more (opens a new window)
                    </a>.
                </small>
            </div>
            <div className={classNames({ hidden: this.state.currentTab !== Tab.SyntaxTab })}>
                {this._renderInfo()}
            </div>
            <div className={classNames({ hidden: currentTab !== Tab.PreviewTab })}>
                {currentTab === Tab.PreviewTab && <Markdown parse={true} text={value} />}
            </div>
        </div>;
    }

    private _renderTabs() {
        const {
            currentTab,
            value,
        } = this.state;

        return <ul className="nav nav-tabs">
            <li role="presentation"
                className={classNames({active: currentTab === Tab.EditTab})}>
                <a href="#" onClick={this._onOpenTab(Tab.EditTab)}>Edit</a>
            </li>
            <li role="presentation"
                className={classNames({
                    active: currentTab === Tab.SyntaxTab,
            })}>
                <a href="#" onClick={this._onOpenTab(Tab.SyntaxTab)}>Formatting help</a>
            </li>
            <li role="presentation"
                className={classNames({
                    active: currentTab === Tab.PreviewTab,
                    disabled: isEmptyString(value),
                })}>
                <a href="#" onClick={this._onOpenTab(Tab.PreviewTab)}>Preview</a>
            </li>
        </ul>;
    }

    private _renderInfo() {
        return <React.Fragment>
            <p>
                Markdown is a lightweight markup language with plain text formatting syntax.
                It is designed to make it easy for you to apply formatting to your text with
                minimal impact on your content.
            </p>
            <p>
                We support the following Markdown syntax:
            </p>
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>Syntax</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>*Italics*</code></td>
                        <td><em>Italics</em> for emphasis.</td>
                    </tr>
                    <tr>
                        <td><code>**Bold text**</code></td>
                        <td><b>Bold text</b> for emphasis.</td>
                    </tr>
                    <tr>
                        <td><code>~~Strike-through~~</code></td>
                        <td><s>Strike-through</s></td>
                    </tr>
                    <tr>
                        <td><code>![An exclamation](exclamation-glyph.png)</code></td>
                        <td>
                            <span className="glyphicon glyphicon-exclamation-sign" title="An exclamation" />.
                            Displays an image with an alternate text (if the image fails to load).
                        </td>
                    </tr>
                    <tr>
                        <td><code>[[tree]]</code></td>
                        <td>Link to the dictionary entry for <em>tree</em>.</td>
                    </tr>
                    <tr>
                        <td><code>[Link to trees](https://en.wikipedia.org/wiki/Tree)</code></td>
                        <td>
                            <a href="https://en.wikipedia.org/wiki/Tree" target="_blank">
                                Link to trees
                            </a>
                            . Links to the specified page.
                            Any external and internal addresses may be specified.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <code>
                                * item 1<br />
                                * item 2<br />
                                &nbsp;&nbsp;&nbsp;* sub item 1<br />
                                * item 3
                            </code>
                        </td>
                        <td>
                            <ul>
                                <li>item 1</li>
                                <li>
                                    item 2
                                    <ul>
                                        <li>sub item 1</li>
                                    </ul>
                                </li>
                                <li>item 3</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <code>
                                1. run<br />
                                2. hide
                            </code>
                        </td>
                        <td>
                            <ol>
                                <li>run</li>
                                <li>hide</li>
                            </ol>
                        </td>
                    </tr>
                    <tr>
                        <td><code># Header</code></td>
                        <td>1st level header. <em>Please use with care!</em></td>
                    </tr>
                    <tr>
                        <td><code>## Header</code></td>
                        <td>2nd level header. <em>Please use with care!</em></td>
                    </tr>
                    <tr>
                        <td><code>### Header</code></td>
                        <td>3rd level header. <em>Please use with care!</em></td>
                    </tr>
                    <tr>
                        <td><code>@sindarin|mae govannen!@</code></td>
                        <td>
                            Transcribes <em>mae govannen</em> to <span className="tengwar">{'tlE xr^5{#5$Á'}</span>
                            We use Glaemscribe for transcriptions. Supported modes are: {' '}
                            adunaic, blackspeech, quenya, sindarin-beleriand, sindarin, telerin, and westron.
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                More information about Markdown
                {' '}
                <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                    can be found on Wikipedia
                </a>.
            </p>
        </React.Fragment>;
    }

    private _onMarkdownChange = (ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        this.setState({
            value: ev.target.value,
        });
    }

    private _onOpenTab = (tab: Tab) => (ev: React.MouseEvent<HTMLAnchorElement>) => {
        const {
            currentTab,
        } = this.state;

        ev.preventDefault();

        // Is the tab currently opened?
        if (currentTab === tab) {
            return;
        }

        this.setState({
            currentTab: tab,
        });
    }
}
