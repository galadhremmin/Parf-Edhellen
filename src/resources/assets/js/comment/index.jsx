import React from 'react';
import ReactDOM from 'react-dom';
import EDComments from '../_shared/components/comments';

window.addEventListener('load', function () {
    const commentContainer = document.getElementById('ed-comments');
    const context = commentContainer.dataset['context'];
    const entityId = parseInt(commentContainer.dataset['entityId'], 10);
    const accountId = parseInt(commentContainer.dataset['accountId'], 10);

    ReactDOM.render(
            <div className="ed-comments">
                <EDComments context={context} entityId={entityId} accountId={accountId} />
            </div>,
        commentContainer
    );
});
