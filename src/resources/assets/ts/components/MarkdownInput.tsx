import classNames from 'classnames';
import React from 'react';

import Cache from '@root/utilities/Cache';
import { isEmptyString } from '@root/utilities/func/string-manipulation';
import { fireEvent } from './Component';
import Markdown from './Markdown';
import {
    IComponentConfig,
    IProps,
    IState,
    Tab,
} from './MarkdownInput._types';

const DefaultConfigCacheFactory = () => Cache.withLocalStorage<IComponentConfig>(() => Promise.resolve({
    enter2Paragraph: true,
}), 'components.MarkdownInput.e2p');

export default class MarkdownInput extends React.PureComponent<IProps, IState> {
    public static defaultProps = {
        configCacheFactory: DefaultConfigCacheFactory,
        id: 'markdownBody',
        name: 'markdownBody',
        props: {},
        required: false,
        rows: 15,
        value: '',
    } as Partial<IProps>;

    public state = {
        currentTab: Tab.EditTab,
        enter2Paragraph: true,
    };

    private _config: Cache<IComponentConfig>;

    public componentWillMount() {
        this._config = this.props.configCacheFactory();
    }

    public async componentDidMount() {
        const {
            enter2Paragraph: current,
        } = this.state;

        const actual = await this._config.get();
        if (current !== actual.enter2Paragraph) {
            this.setState({
                enter2Paragraph: actual.enter2Paragraph,
            });
        }
    }

    public render() {
        const {
            value,
        } = this.props;

        const {
            currentTab,
        } = this.state;

        return <div className="clearfix">
            {this._renderTabs()}

            {currentTab === Tab.EditTab && this._renderEditView()}
            {currentTab === Tab.SyntaxTab && this._renderSyntaxView()}
            {currentTab === Tab.PreviewTab && <Markdown parse={true} text={value} />}
        </div>;
    }

    private _renderTabs() {
        const {
            value,
        } = this.props;
        const {
            currentTab,
        } = this.state;

        const disabled = isEmptyString(value);

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
                    disabled,
                })}>
                <a href="#" onClick={this._onOpenTab(Tab.PreviewTab, disabled)}>Preview</a>
            </li>
        </ul>;
    }

    private _renderEditView() {
        const {
            id,
            name,
            required,
            rows,
            value,
        } = this.props;

        const {
            enter2Paragraph,
        } = this.state;

        return <>
            <textarea className="form-control"
                    id={id}
                    name={name}
                    onChange={this._onMarkdownChange}
                    onKeyDown={this._onMarkdownKeyDown}
                    required={required}
                    rows={rows}
                    value={value}
            />
            <div className="checkbox text-right">
                <label>
                    <input type="checkbox" checked={enter2Paragraph} onChange={this._onEnter2ParagraphChange} />
                    Enter key inserts a paragraph (&para;)
                </label>
            </div>
            <small className="pull-right">
                {' Supports Markdown. '}
                <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                    Read more (opens a new window)
                </a>.
            </small>
        </>;
    }

    private _renderSyntaxView() {
        return <>
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
        </>;
    }

    private _triggerChange(value: string) {
        const {
            onChange,
        } = this.props;

        fireEvent(this, onChange, value);
    }

    private _onOpenTab = (tab: Tab, disabled: boolean = false) => (ev: React.MouseEvent<HTMLAnchorElement>) => {
        const {
            currentTab,
        } = this.state;

        ev.preventDefault();

        // Is the tab currently opened?
        if (currentTab === tab || disabled) {
            return;
        }

        this.setState({
            currentTab: tab,
        });
    }

    private _onMarkdownChange = (ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        this._triggerChange(ev.target.value);
    }

    private _onMarkdownKeyDown = (ev: React.KeyboardEvent<HTMLTextAreaElement>) => {
        const {
            enter2Paragraph,
        } = this.state;

        // special behavior for enter key: insert paragraph, unless shift key is pressed
        if (enter2Paragraph === false || ev.shiftKey) {
            return;
        }

        const target = (ev.target as HTMLTextAreaElement);
        const pos = target.selectionStart;

        if (ev.which === 13 && pos !== undefined) {
            ev.preventDefault();
            ev.stopPropagation();

            const value = target.value.substr(0, pos) + `\n\n` + target.value.substr(pos + 1);
            this._triggerChange(value);
        }
    }

    private _onEnter2ParagraphChange = async (ev: React.ChangeEvent<HTMLInputElement>) => {
        const enter2Paragraph = ev.target.checked;
        const current = await this._config.get();

        this._config.set({
            ...current,
            enter2Paragraph,
        });
        this.setState({
            enter2Paragraph,
        });
    }
}
