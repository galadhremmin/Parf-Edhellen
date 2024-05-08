import {
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';

import Cache from '@root/utilities/Cache';
import { IComponentEvent } from '../../Component._types';
import {
    IComponentConfig,
    IProps,
    Tab,
} from './MarkdownInput._types';
import Tabs from './Tabs';
import EditTabView from './Tabs/EditTabView';
import PreviewTabView from './Tabs/PreviewTabView';
import SyntaxTabView from './Tabs/SyntaxTabView';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import './MarkdownInput.scss';

const DefaultConfigCacheFactory = () => Cache.withLocalStorage<IComponentConfig>(() => Promise.resolve({
    enter2Paragraph: true,
}), 'components.MarkdownInput.config');

function MarkdownInput(props: IProps) {
    const {
        markdownApi,
        configCacheFactory = DefaultConfigCacheFactory,
        value = '',

        id = 'markdownBody',
        required = false,
        rows = 15,

    } = props;

    const [ currentTab, setCurrentTab ] = useState(Tab.EditTab);
    const [ enter2Paragraph, setEnter2Paragraph ] = useState(true);

    const configCacheRef = useRef<Cache<IComponentConfig>>();

    useEffect(() => {
        configCacheRef.current = configCacheFactory();
        configCacheRef.current?.get().then((config) => {
            setEnter2Paragraph(config.enter2Paragraph);
        });
    }, []);

    const _onOpenTab = (ev: IComponentEvent<Tab>) => {
        setCurrentTab(ev.value);
    }

    const _onEnter2ParagraphChange = useCallback(async (ev: IComponentEvent<boolean>) => {
        setEnter2Paragraph(ev.value);

        const config = await configCacheRef.current?.get();
        configCacheRef.current?.set({
            ...config,
            enter2Paragraph: ev.value,
        });
    }, []);

    return <div className="MarkdownInput">
        <Tabs tab={currentTab} onTabChange={_onOpenTab} />
        {currentTab === Tab.EditTab && <EditTabView
            {...props}
            id={id}
            required={required}
            rows={rows}
            enter2Paragraph={enter2Paragraph}
            onEnter2ParagraphChange={_onEnter2ParagraphChange}
            markdownApi={markdownApi}
        />}
        {currentTab === Tab.SyntaxTab && <SyntaxTabView />}
        {currentTab === Tab.PreviewTab && <PreviewTabView value={value} markdownApi={markdownApi} />}
    </div>;
}

export default withPropInjection(MarkdownInput, {
    markdownApi: DI.UtilityApi,
});
