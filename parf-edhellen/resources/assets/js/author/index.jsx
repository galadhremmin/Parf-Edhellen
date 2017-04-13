import React from 'react';
import ReactDOM from 'react-dom';
import EDMarkdownEditor from '../_shared/components/markdown-editor';

window.addEventListener('load', function () {
    const textareas = document.querySelectorAll('textarea.ed-markdown-editor');

    for (let textarea of textareas) {
        ReactDOM.render(
            <EDMarkdownEditor value={'Hello world'} componentName={textarea.name} value={textarea.value}
                rows={textarea.rows} />,
            textarea.parentNode
        );
    }
});
