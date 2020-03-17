import { expect } from 'chai';

import {
    ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import {
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

    it('transcribes a simple sentence without tengwar', async () => {
        const input = 'mae govannen mellon!';
        const expected = [{
            fragment: 'mae',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'govannen',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'mellon',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 1,
            sentenceNumber: 1,
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
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'b',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: 'c',
            paragraphNumber: 1,
            sentenceNumber: 2,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 1,
            sentenceNumber: 2,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: 'd',
            paragraphNumber: 1,
            sentenceNumber: 3,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 1,
            sentenceNumber: 3,
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
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: 'b',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 1,
            sentenceNumber: 1,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: '',
            paragraphNumber: 1,
            sentenceNumber: 2,
            tengwar: null,
            type: SentenceFragmentType.NewLine,
        }, {
            fragment: 'c',
            paragraphNumber: 2,
            sentenceNumber: 2,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 2,
            sentenceNumber: 2,
            tengwar: null,
            type: SentenceFragmentType.Interpunctuation,
        }, {
            fragment: '',
            paragraphNumber: 2,
            sentenceNumber: 3,
            tengwar: null,
            type: SentenceFragmentType.NewLine,
        }, {
            fragment: 'd',
            paragraphNumber: 3,
            sentenceNumber: 3,
            tengwar: null,
            type: SentenceFragmentType.Word,
        }, {
            fragment: '!',
            paragraphNumber: 3,
            sentenceNumber: 3,
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
                paragraphNumber: 1,
                sentenceNumber: 1,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'B',
                paragraphNumber: 1,
                sentenceNumber: 1,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'Changes',
                paragraphNumber: 1,
                sentenceNumber: 1,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: '!',
                paragraphNumber: 1,
                sentenceNumber: 1,
                tengwar: null,
                type: SentenceFragmentType.Interpunctuation,
            }, {
                fragment: '',
                paragraphNumber: 1,
                sentenceNumber: 2,
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
                paragraphNumber,
                sentenceNumber,
                tengwar: null,
                type: SentenceFragmentType.NewLine,
            },
            {
                fragment: 'A',
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'B',
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: 'Changes',
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
                tengwar: null,
                type: SentenceFragmentType.Word,
            }, {
                fragment: '!',
                paragraphNumber: paragraphNumber + 1,
                sentenceNumber,
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
                paragraphNumber: 2,
                sentenceNumber: 2,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: 'H',
                paragraphNumber: 2,
                sentenceNumber: 2,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: 'I',
                paragraphNumber: 2,
                sentenceNumber: 2,
                tengwar: null,
                type: SentenceFragmentType.Word,
            },
            {
                fragment: '.',
                paragraphNumber: 2,
                sentenceNumber: 2,
                tengwar: null,
                type: SentenceFragmentType.Interpunctuation,
            },
            {
                fragment: '',
                paragraphNumber: 2,
                sentenceNumber: 3,
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
