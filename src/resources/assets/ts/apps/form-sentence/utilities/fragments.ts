import {
    type ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { stringHash } from '@root/utilities/func/hashing';

const NewLinesCharacters          = '\n';
const FullStopCharacters          = '.!?';
const InterpunctuationCharacters  = `${FullStopCharacters},;`;
const ConnectionsCharacters       = '-·';
const OpenParanthesisCharacters   = '([';
const CloseParanthesisCharacters  = ')]';

const _isFragmentFullStop = (fragment: string) => {
    return FullStopCharacters.indexOf(fragment) > -1;
};

const _areFragmentsSame = (f0: ISentenceFragmentEntity, f1: ISentenceFragmentEntity) => {
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

const _mergeFragment = (newFragment: ISentenceFragmentEntity, oldFragment: ISentenceFragmentEntity) => ({
    ...newFragment,
    ...oldFragment,
    fragment: newFragment.fragment,
    paragraphNumber: newFragment.paragraphNumber,
    sentenceNumber: newFragment.sentenceNumber,
    tengwar: newFragment.tengwar || oldFragment.tengwar,
});

const _mergeForward = (newFragments: ISentenceFragmentEntity[], oldFragments: ISentenceFragmentEntity[]) => {
    let i = 0;
    for (; i < newFragments.length && i < oldFragments.length && //
        _areFragmentsSame(newFragments[i], oldFragments[i]); i += 1) {
        newFragments[i] = _mergeFragment(newFragments[i], oldFragments[i]);
    }

    return i - 1; // -1 because of the for-loop adding +1 with every iteration
};

const _mergeBackward = (newFragments: ISentenceFragmentEntity[], oldFragments: ISentenceFragmentEntity[], untilIndex: number) => {
    let offset = 0;
    let end;
    while (offset < newFragments.length && offset < oldFragments.length) {
        const newI = newFragments.length - offset - 1;
        const oldI = oldFragments.length - offset - 1;

        if (oldI === untilIndex || ! _areFragmentsSame(newFragments[newI], oldFragments[oldI])) {
            if (end === undefined) {
                end = newFragments.length; // the entire set is modified
            }
            break;
        } else {
            end = newI;
            newFragments[newI] = _mergeFragment(newFragments[newI], oldFragments[oldI]);
            offset += 1;
        }
    }

    return end;
};

const _greedyMerge = (newFragments: ISentenceFragmentEntity[], oldFragments: ISentenceFragmentEntity[], bounds: [number, number]) => {
    const catalog = new Map<string, Map<number, ISentenceFragmentEntity>>();

    // tslint:disable-next-line: variable-name
    const __createHash = (...fragments: ISentenceFragmentEntity[]) => {
        const s = fragments.map((f) => f ? f.fragment.toLocaleLowerCase() : '')
            .join('|');

        return stringHash(s);
    };

    // build a catalogue of fragments and their adjacencies
    for (let i = 0; i < oldFragments.length; i += 1) {
        const f = oldFragments[i];

        if (f.type !== SentenceFragmentType.Word) {
            continue;
        }

        let adjacentCatalog: Map<number, ISentenceFragmentEntity>;
        if (catalog.has(f.fragment)) {
            adjacentCatalog = catalog.get(f.fragment);
        } else {
            adjacentCatalog = new Map();
            catalog.set(f.fragment, adjacentCatalog);
        }

        const lf = oldFragments[i - 1]; // don't care if they are out of bounds. Undefined is fine.
        const rf = oldFragments[i + 1];
        const hashKeys = [
            __createHash(f, rf),
            __createHash(lf, f),
            __createHash(lf, f, rf),
        ];

        for (const hashKey of hashKeys) {
            if (adjacentCatalog.has(hashKey)) {
                const f0 = adjacentCatalog.get(hashKey);
                // TODO - pick the most 'complete' fragment
            } else {
                adjacentCatalog.set(hashKey, f);
            }
        }
    }

    for (let i = bounds[0]; i <= bounds[1]; i += 1) {
        const lf = newFragments[i - 1]; // don't care if they are out of bounds. Undefined is fine.
        const f  = newFragments[i];
        const rf = newFragments[i + 1];

        // unrecognised fragment -- it did not exist in the original set.
        if (f.type !== SentenceFragmentType.Word || ! catalog.has(f.fragment)) {
            continue;
        }

        const adjacentCatalog = catalog.get(f.fragment);
        const hashKeys = [
            __createHash(lf, f, rf), // ordered by priority
            __createHash(f, rf),
            __createHash(lf, f),
        ];
        let found = false;
        for (const hashKey of hashKeys) {
            if (adjacentCatalog.has(hashKey)) {
                newFragments[i] = _mergeFragment(newFragments[i], adjacentCatalog.get(hashKey));
                found = true;
                break;
            }
        }

        if (! found) {
            // just grab the first fragment:
            newFragments[i] = _mergeFragment(newFragments[i], adjacentCatalog.values().next().value);
        }
    }
};

const _updateOrder = (fragments: ISentenceFragmentEntity[]) => {
    let paragraphNumber = 1;
    let sentenceNumber = 1;
    for (const fragment of fragments) {
        fragment.paragraphNumber = paragraphNumber;
        fragment.sentenceNumber = sentenceNumber;

        switch (fragment.type) {
            case SentenceFragmentType.NewLine:
                paragraphNumber += 1;
                break;
            case SentenceFragmentType.Interpunctuation:
                if (_isFragmentFullStop(fragment.fragment)) {
                    sentenceNumber += 1;
                }
                break;
        }
    }

    return fragments;
};

export const createFragment = async (fragment: string, type: SentenceFragmentType, sentenceNumber: number,
    paragraphNumber: number, tengwarMode: string = null): Promise<ISentenceFragmentEntity> => {
    let tengwar: string = null;

    if (tengwarMode) {
        const transcriber = resolve(DI.Glaemscribe);
        tengwar = await transcriber.transcribe(fragment, tengwarMode);
    }

    return {
        fragment,
        lexicalEntryId: 0,
        lexicalEntryInflections: [],
        paragraphNumber,
        speechId: 0,
        sentenceNumber,
        tengwar,
        type,
    } as ISentenceFragmentEntity;
};

export const parseFragments = async (text: string, tengwarMode: string = null) => {
    const newFragments = [];

    // Split the phrase into fragments
    const phrase = text.replace(/\r\n/g, '\n');

    let buffer = '';
    let flush = false;
    let additionalFragment;

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
        else if (InterpunctuationCharacters.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.Interpunctuation,
                sentenceNumber, paragraphNumber, tengwarMode);
            flush = true;

            if (_isFragmentFullStop(c)) {
                newSentence = true;
            }
        }

        // ... a new line?
        else if (NewLinesCharacters.indexOf(c) > -1) {
            additionalFragment = await createFragment('', SentenceFragmentType.NewLine,
                sentenceNumber, paragraphNumber);
            flush = true;

            newParagraph = true;
        }

        // ... or a word connexion?
        else if (ConnectionsCharacters.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.WordConnection,
                sentenceNumber, paragraphNumber);
            flush = true;
        }

        // ... or open paranthesis?
        else if (OpenParanthesisCharacters.indexOf(c) > -1) {
            additionalFragment = await createFragment(c, SentenceFragmentType.OpenParanthesis,
                sentenceNumber, paragraphNumber, tengwarMode);
            flush = true;
        }

        // ... or close paranthesis?
        else if (CloseParanthesisCharacters.indexOf(c) > -1) {
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

    // forward search -- clone from oldFragments until the first divergence
    const forwardEnd = _mergeForward(newFragments, oldFragments);

    // fragments are identical so no further action will be necessary
    if (forwardEnd === newFragments.length - 1) {
        return newFragments;
    }

    // reverse search -- clone from oldFragments until the first divergence
    const backwardEnd = _mergeBackward(newFragments, oldFragments, forwardEnd);

    if (backwardEnd !== undefined && backwardEnd !== forwardEnd) {
        // greedy search -- changes were performed throughout the text body which consequently
        // breaks the forward and backward search algorithms. So the only way forward is to go
        // greedy: match on words where possible with the `fragment` as the only eligible identifier.
        // this will maintain any existing associations the user may have done.
        _greedyMerge(newFragments, oldFragments, [ forwardEnd + 1, backwardEnd - 1 ]);
    }

    // reassign paragraphNumber and sentenceNumber because the sets are partially merged
    return _updateOrder(newFragments);
};
