const toggleClick = (subject: HTMLElement, ev: MouseEvent) => {
    ev.preventDefault();

    let targets: NodeListOf<HTMLElement> | HTMLElement[];
    let className: string;

    if (subject.classList.contains('dropdown-toggle')) {
        className = 'open';
        targets = [ subject.parentElement ];
    } else {
        className = subject.dataset.toggle;
        targets = document.querySelectorAll<HTMLElement>(subject.dataset.target);
    }

    targets.forEach((target: HTMLElement) => {
        target.classList.toggle(className);
    });

    subject.classList.toggle('open');
};

const hookToggle = (toggle: HTMLElement) => {
    toggle.addEventListener('click', toggleClick.bind(window, toggle));
};

const hookNavbarToggles = () => {
    const toggles = document.querySelectorAll<HTMLElement>('.navbar-toggle');
    toggles.forEach((toggle) => {
        hookToggle(toggle);
    });
};

const hookDropdownToggles = () => {
    const toggles = document.querySelectorAll<HTMLElement>('.dropdown-toggle');
    toggles.forEach((toggle) => {
        hookToggle(toggle);
    });
};

export const hookBootstrapToggles = () => {
    hookNavbarToggles();
    hookDropdownToggles();
};
