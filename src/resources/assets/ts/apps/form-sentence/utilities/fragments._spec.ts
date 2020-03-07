import { expect } from 'chai';

import {
    ISentenceFragmentEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import { parseFragments } from './fragments';

describe('apps/form-sentence/utilities/fragments', () => {
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
            fragment: '\n',
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
            fragment: '\n',
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
});
