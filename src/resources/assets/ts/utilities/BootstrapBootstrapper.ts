function shimRequestIdleCallback() {
    // Currently not supported by Safari, so shimming it to make it work as intended (kind of...)
    window.requestIdleCallback = window.requestIdleCallback ||
        function (cb: IdleRequestCallback): number {
            const start = Date.now();
            return setTimeout(function () {
                cb({
                    didTimeout: false,
                    timeRemaining: function () {
                        return Math.max(0, 50 - (Date.now() - start));
                    }
                });
            }, 1) as unknown as number;
        };

    window.cancelIdleCallback = window.cancelIdleCallback || //
        function (id) {
            clearTimeout(id);
        }
}

function closeOtherDropdowns(excludeMenu: HTMLUListElement) {
    document.querySelectorAll('.dropdown-menu.show').forEach((menu) => {
        if (menu !== excludeMenu) {
            menu.classList.remove('show');
            const dropdown = menu.closest('.dropdown');
            if (dropdown) {
                const trigger = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                if (trigger) {
                    trigger.classList.remove('open');
                }
            }
        }
    });
}

function onBsToggle(target: HTMLUListElement, ev: MouseEvent) {
    ev.preventDefault();
    ev.stopPropagation();

    const isOpening = !target.classList.contains('show');
    if (isOpening) {
        closeOtherDropdowns(target);
    }

    target.classList.toggle('show');

    const triggerElement = this as HTMLElement;
    triggerElement.classList.toggle('open');
}

function onDocumentClick(ev: MouseEvent) {
    const target = ev.target as Node;
    document.querySelectorAll('.dropdown-menu.show').forEach((menu) => {
        const dropdown = menu.closest('.dropdown');
        if (dropdown && !dropdown.contains(target)) {
            menu.classList.remove('show');
            const trigger = dropdown.querySelector('[data-bs-toggle="dropdown"]');
            if (trigger) {
                trigger.classList.remove('open');
            }
        }
    });
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

            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            element.addEventListener('click', onBsToggle.bind(element, target));
        } catch (ex) {
            console.warn([`[Bootstrap] Failed to initialize component behavior. Error: ${ex}`, element]);
        }
    }
}

export default function bootstrapServerSideRenderedBootstrapComponents() {
    shimRequestIdleCallback();
    bootstrapBsToggle();
    document.addEventListener('click', onDocumentClick);
}
