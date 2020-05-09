/**
 * Adjusts the viewport so that the specified DOM element's bounding box is within it and visible.
 * @param domElement
 */
export const makeVisibleInViewport = (domElement: Element, offsetsXY: [number, number] = null) => {
    if (! domElement) {
        return;
    }

    requestAnimationFrame(() => {
        if (offsetsXY === null) {
            domElement.scrollIntoView({
                block: 'start',
                behavior: 'smooth',
            });
        } else {
            // This is an alternate implementation intended to support X and Y offsets. Offsets are
            // not currently supported by `scrollIntoView`.
            const rect = domElement.getBoundingClientRect();
            const y = rect.top + window.pageYOffset + (offsetsXY[1] || 0);
            const x = rect.left + window.pageXOffset + (offsetsXY[0] || 0);
            window.scrollTo({
                left: x,
                top: y,
                behavior: 'smooth',
            });
        }
    });
};
