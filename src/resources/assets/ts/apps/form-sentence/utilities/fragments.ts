import {
    DI,
    resolve,
} from '@root/di';
import {
    ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import Glaemscribe from '@root/utilities/Glaemscribe';

const createFragment = async (fragment: string, type: SentenceFragmentType, sentenceNumber: number,
    paragraphNumber: number, tengwarMode: string = null): Promise<ISentenceFragmentEntity> => {
    let tengwar: string = null;

    if (tengwarMode) {
        const transcriber = resolve<Glaemscribe>(DI.Glaemscribe);
        tengwar = await transcriber.transcribe(fragment, tengwarMode);
    }

    return {
        fragment,
        paragraphNumber,
        sentenceNumber,
        tengwar,
        type,
    };
};

export const parseFragments = async (text: string, tengwarMode: string = null) => {
    const newFragments = [];

    // Split the phrase into fragments
    const phrase = text.replace(/\r\n/g, '\n');

    let buffer = '';
    let flush = false;
    let additionalFragment;

    const newlines          = '\n';
    const fullStop          = '.!?';
    const interpunctuations = `${fullStop},;`;
    const connections       = '-·';
    const openParanthesis   = '([';
    const closeParanthesis  = ')]';

    let sentenceNumber = 1;
    let paragraphNumber = 1;

    let newSentence = false;
    let newParagraph = false;

    for (const c of phrase) {

        // space?
        if (c === ' ') {
            flush = true;
        }

        // is it an interpunctuation character?
        else if (interpunctuations.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.Interpunctuation,
                sentenceNumber, paragraphNumber, tengwarMode);
            flush = true;

            if (fullStop.indexOf(c) > -1) {
                newSentence = true;
            }
        }

        // ... a new line?
        else if (newlines.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.NewLine,
                sentenceNumber, paragraphNumber);
            flush = true;

            newParagraph = true;
        }

        // ... or a word connexion?
        else if (connections.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.WordConnection,
                sentenceNumber, paragraphNumber);
            flush = true;
        }

        // ... or open paranthesis?
        else if (openParanthesis.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.OpenParanthesis,
                sentenceNumber, paragraphNumber, tengwarMode);
            flush = true;
        }

        // ... or close paranthesis?
        else if (closeParanthesis.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.CloseParanthesis,
                sentenceNumber, paragraphNumber, tengwarMode);
            flush = true;
        }

        // add regular characters to buffer
        else {
            buffer += c;
        }

        if (flush) {
            if (buffer.length > 0) {
                newFragments.push(await createFragment(buffer, SentenceFragmentType.Word,
                    sentenceNumber, paragraphNumber, tengwarMode));
                buffer = '';
            }

            if (additionalFragment) {
                newFragments.push(additionalFragment);
                additionalFragment = undefined;
            }

            flush = false;
        }

        if (newSentence) {
            sentenceNumber += 1;
            newSentence = false;
        }

        if (newParagraph) {
            paragraphNumber += 1;
            newParagraph = false;
        }
    }

    if (buffer.length > 0) {
        newFragments.push(await createFragment(buffer, SentenceFragmentType.Word,
            sentenceNumber, paragraphNumber, tengwarMode));
    }

    return newFragments;
};
