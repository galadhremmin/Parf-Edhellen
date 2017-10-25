window.addEventListener('load', function() {
    // Find all elements with the _date_ class, retrieve their text content
    // (which is assumed to be a valid UTC-date) and transform it into a localized
    // date format.
    const dateElements = document.querySelectorAll('.date');
    
    for (var i = dateElements.length - 1; i >= 0; i -= 1) {
        const dateElement = dateElements.item(i);
        const dateString = dateElement.textContent || dateElement.innerText;

        // if the element contains children, it is safe to assume it is not (in fact)
        // formatted as we would come to expect it. Skip it, if it is.
        if (!dateString || dateElement.children.length > 0) {
            continue;
        }

        dateElement.textContent = (new Date(dateString)).toLocaleString();
    }
});
