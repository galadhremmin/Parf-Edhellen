export const capitalize = (words: string) => typeof words === 'string' //
    ? words.split(' ').map((w: string) => w.substr(0, 1).toLocaleUpperCase() + w.substr(1)).join(' ') //
    : null;
