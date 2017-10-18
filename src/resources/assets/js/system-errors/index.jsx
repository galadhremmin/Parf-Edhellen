import React from 'react';
import ReactDOM from 'react-dom';
import EDSystemErrorList from './components/error-list';

window.addEventListener('load', function () {
    const container = document.getElementById('ed-errors');
    
    if (container) {
        const dataContainer = document.getElementById('ed-preloaded-errors');
        const data = JSON.parse(dataContainer.textContent);

        ReactDOM.render(
            <EDSystemErrorList errors={data} />,
            container
        );
    }
});
