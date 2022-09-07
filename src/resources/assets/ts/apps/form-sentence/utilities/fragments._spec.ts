import { expect } from 'chai';

import {
    ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import {
    createFragment,
    parseFragments,
    mergeFragments,
} from './fragments';

describe('apps/form-sentence/utilities/fragments', () => {
    let testData: {
        sentenceFragments: ISentenceFragmentEntity[];
        text: string;
    };

    before(() => {
        testData = require('./fragments._spec.json');
    });

    it('creates valid fragments', async () => {
        const fragment = 'A';
        const paragraphNumber = 100;
        const sentenceNumber = 50;
        const type = SentenceFragmentType.Word;

        const actual = await createFragment(fragment, type, sentenceNumber, paragraphNumber);
        const expected = {
            fragment,
            glossId: 0,
            inflections: [],
            paragraphNumber,
            sentenceNumber,
            speechId: 0,
            tengwar: null,
            type,
        } as ISentenceFragmentEntity;

        expect(actual).to.deep.equal(expected);
    });

    it('is case sensitive', async () => {
        const oldText = 'A B C';
        const newText = 'a b c';

        const oldFragments = await parseFragments(oldText);
        oldFragments.forEach((f) => {
            f.comments = (Math.random() * 10000).toString(10);
        });

        const newFragments = await parseFragments(newText);

        const actual = mergeFragments(newFragments, oldFragments);
        const expected = oldFragments.map((f) => {
            f.fragment = f.fragment.toLocaleLowerCase();
            return f;
        });

        expect(actual).to.deep.equal(expected);
    });

    it('transcribes a simple sentence without tengwar', async () => {
        const input = 'mae govannen mellon!';
        const expected = [{
            fragment: 'mae',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'govannen',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'mellon',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }] as ISentenceFragmentEntity[];
        const actual = await parseFragments(input);

        expect(actual).to.deep.equal(expected);
    });

    it('handles multiple sentences', async () => {
        const input = 'a b! c! d!';
        const expected = [{
            fragment: 'a',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'b',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: 'c',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 2,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 2,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: 'd',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 3,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 3,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }] as ISentenceFragmentEntity[];
        const actual = await parseFragments(input);

        expect(actual).to.deep.equal(expected);
    });

    it('handles multiple sentences and paragraphs', async () => {
        const input = 'a b!\nc!\nd!';
        const expected = [{
            fragment: 'a',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'b',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 1,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: '',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 1,
            sentenceNumber: 2,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.NewLine,
        }, {
            fragment: 'c',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 2,
            sentenceNumber: 2,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 2,
            sentenceNumber: 2,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: '',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 2,
            sentenceNumber: 3,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.NewLine,
        }, {
            fragment: 'd',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 3,
            sentenceNumber: 3,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            glossId: 0,
            glossInflections: [],
            paragraphNumber: 3,
            sentenceNumber: 3,
            speechId: 0,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }] as ISentenceFragmentEntity[];
        const actual = await parseFragments(input);

        expect(actual).to.deep.equal(expected);
    });

    it('restores original set when no changes were made', async () => {
        const fragments = await parseFragments(testData.text, null);
        const actual = mergeFragments(fragments, testData.sentenceFragments);
        const expected = testData.sentenceFragments;

        expect(actual).to.deep.equal(expected);
    });

    it('merges fragments when forward changes were made to the original set', async () => {
        const modifiedText = `A B Changes!\n${testData.text}`;
        const modifiedFragments = await parseFragments(modifiedText, null);
        const originalFragments = testData.sentenceFragments.map((f) => ({
            ...f,
            paragraphNumber: f.paragraphNumber + 1,
            sentenceNumber: f.sentenceNumber + 1,
        }));
        const actual = mergeFragments(modifiedFragments, originalFragments);
        const expected = [{
                fragment: 'A',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 1,
                sentenceNumber: 1,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'B',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 1,
                sentenceNumber: 1,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'Changes',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 1,
                sentenceNumber: 1,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: '!',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 1,
                sentenceNumber: 1,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Interpunctuation,
            }, {
                fragment: '',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 1,
                sentenceNumber: 2,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.NewLine,
            },
            ...originalFragments,
        ];

        expect(actual).to.deep.equal(expected);
    });

    it('merges fragments when rear changes were made to the original set', async () => {
        const modifiedText = `${testData.text}\nA B Changes!`;
        const modifiedFragments = await parseFragments(modifiedText, null);
        const originalFragments = testData.sentenceFragments;
        const actual = mergeFragments(modifiedFragments, originalFragments);
        const {
            paragraphNumber,
            sentenceNumber,
        } = originalFragments[originalFragments.length - 1];
        const expected = [
            ...originalFragments,
            {
                fragment: '',
                glossId: 0,
                glossInflections: [],
                paragraphNumber,
                sentenceNumber,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.NewLine,
            },
            {
                fragment: 'A',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'B',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'Changes',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: '!',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Interpunctuation,
            },
        ];

        expect(actual).to.deep.equal(expected);
    });

    it('merges fragments when middle changes were made to the original set', async () => {
        // this is a sophisticated and moderately complicated use case:
        // the user is making changes within the text body (i.e. adding and/or removing fragments).
        // it is essential that we maintain existing fragments to the greatest degree possible.
        const oldText = 'A B C!\nD E F?';
        const newText = 'A B C!\nG H I.\nD E F?';

        const [ oldFragments, newFragments ] = await Promise.all([
            parseFragments(oldText, null),
            parseFragments(newText, null),
        ]);

        // add some random comments to old fragments to make it possible to look for original fragments:
        oldFragments.forEach((f) => {
            f.comments = (Math.random() % 1000).toString(10);
        });

        const actual = mergeFragments(newFragments, oldFragments);
        const expected = [
            ...oldFragments.slice(0, 5),
            {
                fragment: 'G',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 2,
                sentenceNumber: 2,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: 'H',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 2,
                sentenceNumber: 2,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: 'I',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 2,
                sentenceNumber: 2,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: '.',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 2,
                sentenceNumber: 2,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.Interpunctuation,
            },
            {
                fragment: '',
                glossId: 0,
                glossInflections: [],
                paragraphNumber: 2,
                sentenceNumber: 3,
                speechId: 0,
                tengwar: null,
                type: SentenceFragmentType.NewLine,
            },
            ...oldFragments.slice(5).map((f) => ({
                ...f,
                paragraphNumber: 3,
                sentenceNumber: 3,
            })),
        ];
        expect(actual).to.deep.equal(expected);
    });

    it('merges fragments for dispersed changes to the original set', async () => {
        // This is the use case where the user has integrated changes throughout the
        // original text.
        const oldText = 'mellon i vellon i mellyn';
        const newText = 'gandalf mellon i mellyn i vellon i mellyn i vellon mellon mellyn dunadan';

        const [ oldFragments, newFragments ] = await Promise.all([
            parseFragments(oldText, null),
            parseFragments(newText, null),
        ]);

        oldFragments.map((f, i) => {
            f.comments = `${f.fragment} (${(Math.random() * 10000).toString(10)}) #${i}`;
        });

        const actual = mergeFragments(newFragments, oldFragments);
        const expected = [
            newFragments[0], // gandalf,
            oldFragments[0],  // mellon
            oldFragments[3],  // i [mellyn]
            oldFragments[4],  // mellyn
            oldFragments[1],  // i [vellon]
            oldFragments[2],  // vellon
            oldFragments[3],  // i [mellyn]
            oldFragments[4],  // mellyn
            oldFragments[1],  // i [vellon]
            oldFragments[2],  // vellon
            oldFragments[0],  // mellon
            oldFragments[4],  // mellyn
            newFragments[12], // dunadan
        ];

        expect(actual).to.deep.equal(expected);
    });
});
