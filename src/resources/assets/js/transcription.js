import { transcribe } from 'ed-tengwar';

const load = () => {
    const allElems = document.querySelectorAll('.tengwar[data-tengwar-transcribe]');
    for (var i = 0; i < allElems.length; i += 1) {
        const elem = allElems.item(i);
        const text = elem.textContent;
        const mode = elem.dataset.tengwarMode;

        if (mode) {
            elem.textContent = transcribe(text, mode);
        }
    }
};

load();
