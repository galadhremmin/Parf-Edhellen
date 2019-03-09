/**
 * Adjusts the viewport so that the specified DOM element's bounding box is within it and visible.
 * @param component 
 */
export const makeVisibleInViewport = (domElement: any) => {
    requestAnimationFrame(() => {
        domElement.scrollIntoView({
            block: 'start',
        });
    });
};