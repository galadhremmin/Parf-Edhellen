import scrollIntoView from 'scroll-into-view';

export const smoothScrollIntoView = element => {
    if (! element) {
        return;
    }

    scrollIntoView(element, {
        time: 500,
        align: {
            top: 0,
            topOffset: -40 /* pixels */
        }
    });
};
