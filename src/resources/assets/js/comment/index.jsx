import React from 'react';
import ReactDOM from 'react-dom';
import EDComments from 'ed-components/comments';
import loadGlaemscribe from '../_shared/glaemscribe-loader';

const init = container => {
    const morph = container.dataset['morph'];
    const entityId = parseInt(container.dataset['entityId'], 10);
    const accountId = parseInt(container.dataset['accountId'], 10);
    const enabled = /^true$/i.test(container.dataset['postEnabled'] || '');
    const order = container.dataset['postOrder'] || undefined;

    ReactDOM.render(
            <div className="ed-comments">
                <EDComments morph={morph} entityId={entityId} accountId={accountId} enabled={enabled}
                    order={order} />
            </div>,
        container
    );
};

const load = () => {
    Array.prototype.slice.call(document.getElementsByClassName('ed-comments-container'))
        .forEach(init.bind(this));
};

window.addEventListener('load', function () {
    loadGlaemscribe().then(load);
});
