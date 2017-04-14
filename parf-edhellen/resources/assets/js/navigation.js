(function () {
    const onButtonClick = ev => {
        ev.preventDefault();

        const targets = document.querySelectorAll(ev.target.dataset.target);
        const className = ev.target.dataset.toggle;
        for (let target of targets) {
            if (target.classList.contains(className)) {
                target.classList.remove(className);
            } else {
                target.classList.add(className);
            }
        }
    };

    const buttons = document.querySelectorAll('.navbar-toggle');
    for (let button of buttons) {
        button.addEventListener('click', ev => onButtonClick(ev));
    }
})();
