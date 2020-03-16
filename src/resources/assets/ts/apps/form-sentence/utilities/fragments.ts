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
            additionalFragment = await createFragment('', SentenceFragmentType.NewLine,
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

export const mergeFragments = (newFragments: ISentenceFragmentEntity[], oldFragments: ISentenceFragmentEntity[]) => {
    if (newFragments.length < 1 || oldFragments.length < 1) {
        return;
    }

    const areFragmentsSame = (f0: ISentenceFragmentEntity, f1: ISentenceFragmentEntity) => {
        if (f0.type !== f1.type) {
            return false;
        }

        switch (f0.type) {
            case SentenceFragmentType.Word:
                return f0.fragment.toLocaleLowerCase() === f1.fragment.toLocaleLowerCase();
            case SentenceFragmentType.Interpunctuation:
            case SentenceFragmentType.OpenParanthesis:
            case SentenceFragmentType.CloseParanthesis:
                return f0.fragment === f1.fragment;
            default:
                return true;
        }
    };

    // forward search -- clone from oldFragments until the first divergence
    let start = 0;
    for (; start < newFragments.length && start < oldFragments.length && //
        areFragmentsSame(newFragments[start], oldFragments[start]); start += 1) {
        newFragments[start] = { ...oldFragments[start] };
    }

    // fragments are identical so no further action will be necessary
    if (start === newFragments.length) {
        return newFragments;
    }

    // reverse search -- clone from oldFragments until the first divergence
    let offset = 0;
    let end;
    while (offset < newFragments.length && offset < oldFragments.length) {
        const newI = newFragments.length - offset - 1;
        const oldI = oldFragments.length - offset - 1;

        end = oldI;

        if (oldI === start - 1 || ! areFragmentsSame(newFragments[newI], oldFragments[oldI])) {
            break;
        } else {
            newFragments[newI] = { ...oldFragments[oldI] };
            offset += 1;
        }
    }

    if (end !== undefined && end + 1 !== start) {
        // greedy search -- changes were performed throughout the text body which consequently
        // breaks the forward and backward search algorithms. So the only way forward is to go
        // greedy: match on words where possible with the `fragment` as the only eligible identifier.
        // this will maintain any existing associations the user may have done.

        // TODO
    }

    // reassign paragraphNumber and sentenceNumber because the sets are partially merged
    let paragraphNumber = 1;
    let sentenceNumber = 1;
    for (const fragment of newFragments) {
        fragment.paragraphNumber = paragraphNumber;
        fragment.sentenceNumber = sentenceNumber;

        switch (fragment.type) {
            case SentenceFragmentType.NewLine:
                paragraphNumber += 1;
                break;
            case SentenceFragmentType.Interpunctuation:
                sentenceNumber += 1;
                break;
        }
    }

    return newFragments;
};
