import axios from 'axios';
import EDConfig from 'ed-config';

(function () {
    const originalFunc = window.onerror;

    window.onerror = function (message, url, lineNo, columnNo, error) {
        const string = message.toLowerCase();
        const disqualified = "script error";

        if (string.indexOf(disqualified) === -1) {
            const payload = {
                message,
                url: window.location.href,
                error: error ? error.stack : null
            };

            axios.post(EDConfig.api('utility/error'), payload);
        }

        if (typeof originalFunc === 'function') {
            originalFunc(message, url, lineNo, columnNo, error);
        }

        return false;
    };
}());
