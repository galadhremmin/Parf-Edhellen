(function () {
    const findTarget = elem => {
        while (elem) {
            if (/\bnavbar\-toggle\b/.test(elem.className)) {
                return elem;
            }

            elem = elem.parentNode;
        }

        return undefined;
    };

    const onButtonClick = ev => {
        ev.preventDefault();

        const button = findTarget(ev.target);
        const targets = document.querySelectorAll(button.dataset.target);
        const className = button.dataset.toggle;
        
        for (let target of targets) {
            if (target.classList.contains(className)) {
                target.classList.remove(className);
            } else {
                target.classList.add(className);
            }
        }
    };

    const buttons = document.querySelectorAll('.navbar-toggle');
    for (var i = 0; i < buttons.length; i += 1) {
        buttons.item(i).addEventListener('click', ev => {
            onButtonClick(ev)
        });
    }
})();
