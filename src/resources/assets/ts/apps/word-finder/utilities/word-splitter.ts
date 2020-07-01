import { IGloss } from '@root/connectors/backend/IWordFinderApi';

export const preprocessWordForSplitting = (word: string) => //
    word.toLocaleLowerCase().replace(/[\-]/g, '');

export const splitWord = (word: string) => {
    const wordWithoutSpaces = preprocessWordForSplitting(word);
    const parts: string[] = [];

    for (let pos = 0; pos < wordWithoutSpaces.length; ) {
        const remainder = wordWithoutSpaces.length - pos;
        const length = remainder % 3 === 0 ? 3 : 2;
        if (remainder > length) {
            const part = wordWithoutSpaces.substr(pos, length);
            parts.push(part);
            pos += length;
        } else {
            parts.push(wordWithoutSpaces.substr(pos));
            break;
        }
    }

    return parts;
};

export const findGlossFromParts = (glossary: IGloss[], parts: string[]) => {
    const word = parts.join('');
    const gloss = glossary.find((g) => g.word === word);

    return gloss || null;
};
