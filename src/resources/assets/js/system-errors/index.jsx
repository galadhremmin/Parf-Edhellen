import React from 'react';
import ReactDOM from 'react-dom';
import EDSystemErrorList from './components/error-list';

const load = () => {
    const container = document.getElementById('ed-errors');
    
    if (container) {
        const dataContainer = document.getElementById('ed-preloaded-errors');
        const value = JSON.parse(dataContainer.textContent);

        ReactDOM.render(
            <EDSystemErrorList value={value} />,
            container
        );
    }
};

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
