import React from 'react';
import { render } from 'react-dom';
import EDConfig from 'ed-config';
import AdminToolbar from './components/admin-toolbar';
import UserToolbar from './components/user-toolbar';

const renderInChild = (Component, container, className) => {
    const threadId = parseInt(container.dataset['threadId'], 10);

    const ul = document.createElement('ul');
    ul.className = className;
    container.appendChild(ul);

    render(<Component threadId={threadId} />, ul);
}

const load = () => {
    const container = document.getElementById('discuss-toolbar');
    
    if (EDConfig.admin()) {
        renderInChild(AdminToolbar, container, 'admin');
    }

    if (EDConfig.userId()) {
        renderInChild(UserToolbar, container, 'user');
    }
};

load();