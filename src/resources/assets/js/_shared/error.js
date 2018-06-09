import EDAPI from 'ed-api';

const IgnoreList = [ 
    /**
     * This particular exception is excluded as this is a third-party component, and not
     * something which would be raised by our code.
     */
    'GoogleDocsResearchGsaProxy' 
];

(function () {
    const originalFunc = window.onerror;

    window.onerror = function (message, url, lineNo, columnNo, error) {
        const string = message.toLowerCase();
        const disqualified = "script error";

        if (string.indexOf(disqualified) === -1) {
            const message = `${message} (${navigator.appName} ${navigator.appVersion})`;
            const url = window.location.href;
            const stack = error ? error.stack : '';
            
            if (IgnoreList.every(ignore => stack.indexOf(ignore) === -1)) {
                EDAPI.error(message, url, stack);
            }
        }

        if (typeof originalFunc === 'function') {
            originalFunc(message, url, lineNo, columnNo, error);
        }

        return false;
    };
}());
