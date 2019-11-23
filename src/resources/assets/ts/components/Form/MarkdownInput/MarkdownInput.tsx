import React from 'react';

import Cache from '@root/utilities/Cache';

import { fireEvent } from '../../Component';
import { IComponentEvent } from '../../Component._types';
import {
    IComponentConfig,
    IProps,
    IState,
    Tab,
} from './MarkdownInput._types';
import Tabs from './Tabs';
import EditTabView from './Tabs/EditTabView';
import PreviewTabView from './Tabs/PreviewTabView';
import SyntaxTabView from './Tabs/SyntaxTabView';

const DefaultConfigCacheFactory = () => Cache.withLocalStorage<IComponentConfig>(() => Promise.resolve({
    enter2Paragraph: true,
}), 'components.MarkdownInput.config');

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
    constructor(props: IProps) {
        super(props);
        this._config = props.configCacheFactory();
    }

    public async componentDidMount() {
        const {
            enter2Paragraph: current,
        } = this.state;

        const actual = await this._config.get();
        if (current !== actual.enter2Paragraph) {
            this.setState({
                enter2Paragraph: actual.enter2Paragraph || true,
            });
        }
    }

    public render() {
        const {
            value,
        } = this.props;

        const {
            currentTab,
            enter2Paragraph,
        } = this.state;

        return <div className="clearfix">
            <Tabs tab={currentTab} onTabChange={this._onOpenTab} />
            {currentTab === Tab.EditTab && <EditTabView
                {...this.props}
                enter2Paragraph={enter2Paragraph}
                onEnter2ParagraphChange={this._onEnter2ParagraphChange}
            />}
            {currentTab === Tab.SyntaxTab && <SyntaxTabView />}
            {currentTab === Tab.PreviewTab && <PreviewTabView value={value} />}
        </div>;
    }

    private _onOpenTab = (ev: IComponentEvent<Tab>) => {
        const {
            currentTab,
        } = this.state;

        const tab = ev.value;

        // Is the tab currently opened?
        if (currentTab === tab) {
            return;
        }

        this.setState({
            currentTab: tab,
        });
    }

    private _onEnter2ParagraphChange = async (ev: IComponentEvent<boolean>) => {
        const enter2Paragraph = ev.value;
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
