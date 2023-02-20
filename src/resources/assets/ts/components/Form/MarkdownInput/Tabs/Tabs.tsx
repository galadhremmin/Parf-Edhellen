import classNames from 'classnames';
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
        <li className="nav-item">
            <a className={classNames('nav-link', { active: tab === Tab.EditTab })} href="#" onClick={_onTabChange(Tab.EditTab)}>Edit</a>
        </li>
        <li className="nav-item">
            <a className={classNames('nav-link', { active: tab === Tab.SyntaxTab })} href="#" onClick={_onTabChange(Tab.SyntaxTab)}>Formatting</a>
        </li>
        <li className="float-end nav-item">
            <a className={classNames('nav-link', { active: tab === Tab.PreviewTab })} href="#" onClick={_onTabChange(Tab.PreviewTab)}>Preview</a>
        </li>
    </ul>;
}

export default Tabs;
