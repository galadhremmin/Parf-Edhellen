import EDAPI from 'ed-api';

(function () {
    const originalFunc = window.onerror;

    window.onerror = function (message, url, lineNo, columnNo, error) {
        const string = message.toLowerCase();
        const disqualified = "script error";

        if (string.indexOf(disqualified) === -1) {
            const message = `${message} (${navigator.appName} ${navigator.appVersion})`;
            const url = window.location.href;
            const stack = error ? error.stack : null;
            
            EDAPI.error(message, url, stack);
        }

        if (typeof originalFunc === 'function') {
            originalFunc(message, url, lineNo, columnNo, error);
        }

        return false;
    };
}());
