function shimRequestIdleCallback() {
    // Currently not supported by Safari, so shimming it to make it work as intended (kind of...)
    window.requestIdleCallback = window.requestIdleCallback ||
        function (cb: IdleRequestCallback, _options?: IdleRequestOptions): number {
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

function onBsToggle(target: HTMLUListElement, ev: MouseEvent) {
    ev.preventDefault();

    target.classList.toggle('show');

    const triggerElement = this as HTMLElement;
    triggerElement.classList.toggle('open');
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
}
