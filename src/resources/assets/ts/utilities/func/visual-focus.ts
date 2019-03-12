/**
 * Adjusts the viewport so that the specified DOM element's bounding box is within it and visible.
 * @param domElement
 */
export const makeVisibleInViewport = (domElement: any) => {
    if (! domElement) {
        return;
    }

    requestAnimationFrame(() => {
        domElement.scrollIntoView({
            block: 'start',
        });
    });
};
