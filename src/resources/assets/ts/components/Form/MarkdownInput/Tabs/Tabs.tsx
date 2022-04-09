import classNames from 'classnames';
import React from 'react';

import { fireEvent } from '@root/components/Component';
import { Tab } from '../MarkdownInput._types';
import { IProps } from './Tabs._types';

function Tabs(props: IProps) {
    const {
        onTabChange,
        tab,
    } = props;

    const _onTabChange = (newTab: Tab) => (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent('tabs', onTabChange, newTab);
    };

    return <ul className="nav nav-tabs">
        <li role="presentation"
            className={classNames({active: tab === Tab.EditTab})}>
            <a href="#" onClick={_onTabChange(Tab.EditTab)}>Edit</a>
        </li>
        <li role="presentation"
            className={classNames({
                active: tab === Tab.SyntaxTab,
        })}>
            <a href="#" onClick={_onTabChange(Tab.SyntaxTab)}>Formatting</a>
        </li>
        <li role="presentation"
            className={classNames('float-end', {
                active: tab === Tab.PreviewTab,
            })}>
            <a href="#" onClick={_onTabChange(Tab.PreviewTab)}>Preview</a>
        </li>
    </ul>;
}

export default Tabs;
