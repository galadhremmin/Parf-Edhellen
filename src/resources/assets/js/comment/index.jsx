import React from 'react';
import ReactDOM from 'react-dom';
import EDComments from 'ed-components/comments';

window.addEventListener('load', function () {

    const init = container => {
        const morph = container.dataset['morph'];
        const entityId = parseInt(container.dataset['entityId'], 10);
        const accountId = parseInt(container.dataset['accountId'], 10);
        const enabled = /^true$/i.test(container.dataset['postEnabled'] || '');

        ReactDOM.render(
                <div className="ed-comments">
                    <EDComments morph={morph} entityId={entityId} accountId={accountId} enabled={enabled} />
                </div>,
            container
        );
    };

    Array.prototype.slice.call(document.getElementsByClassName('ed-comments-container'))
        .forEach(init.bind(this));
});
