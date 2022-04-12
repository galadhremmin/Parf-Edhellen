function onBsToggle(target: HTMLUListElement, ev: MouseEvent) {
    ev.preventDefault();

    target.classList.toggle('show');
    this.classList.toggle('open');
}

function bootstrapBsToggle() {
    const elements = document.querySelectorAll('[data-bs-toggle]');
    for (let i = 0; i < elements.length; i += 1) {
        const element = elements.item(i) as HTMLElement;
        let target: HTMLElement;

        try {
            const targetSelector = element.dataset.bsTarget;
            if (targetSelector === undefined) {
                if (element.dataset.bsToggle === 'dropdown') {
                    do {
                        target = element.nextElementSibling as HTMLElement;
                    } while (target && ! /^ul$/i.test(target.tagName));
                } else {
                    throw new Error('missing target selector.');
                }
            } else {
                target = document.querySelector(targetSelector);
            }

            if (! target) {
                throw new Error(`${targetSelector} matches no element.`);
            }

            element.addEventListener('click', onBsToggle.bind(element, target));
        } catch (ex) {
            console.warn([`[Bootstrap] Failed to initialize component behavior. Error: ${ex}`, element]);
        }
    }
};

export default function bootstrapServerSideRenderedBootstrapComponents() {
    bootstrapBsToggle();
}
