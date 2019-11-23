const toggleClick = (subject: HTMLElement, ev: MouseEvent) => {
    ev.preventDefault();

    const targetSelector = subject.dataset.target;
    const className = subject.dataset.toggle;

    const targets = document.querySelectorAll<HTMLElement>(targetSelector);
    for (const target of targets) {
        target.classList.toggle(className);
    }
};

const hookToggle = (toggle: HTMLElement) => {
    toggle.addEventListener('click', toggleClick.bind(window, toggle));
};

export const hookBootstrapToggles = () => {
    const toggles = document.querySelectorAll<HTMLElement>('.navbar-toggle');
    for (const toggle of toggles) {
        hookToggle(toggle);
    }
};
