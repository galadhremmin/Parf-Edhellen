/* tslint:disable */
import { expect } from 'chai';

import {
    ISentenceFragmentEntity,
    ITextTransformation,
} from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';

import convert from './TextConverter';

describe('apps/sentence/utilities/TextConverter', () => {
    const MinimumId = 1 << 31;
    const Fragments: ISentenceFragmentEntity[] = snakeCasePropsToCamelCase(
        JSON.parse(`[{"id":3242,"gloss_id":366116,"type":0,"fragment":"A","tengwar":"\`C","speech":"interjection","speech_id":12,"comments":null,"inflections":[]},{"id":3243,"gloss_id":336024,"type":0,"fragment":"T\u00farin","tengwar":"1~M7T5","speech":"masculine name","speech_id":13,"comments":"","inflections":[]},{"id":3244,"gloss_id":369112,"type":0,"fragment":"Turambar","tengwar":"1U7Ew#6","speech":"masculine name","speech_id":13,"comments":null,"inflections":[]},{"id":3245,"gloss_id":115037,"type":0,"fragment":"tur\u00fan\u2019","tengwar":"1U7~M5","speech":"verb","speech_id":26,"comments":"","inflections":[{"id":43,"name":"passive participle"}]},{"id":3246,"gloss_id":103246,"type":0,"fragment":"ambartanen","tengwar":"\`Cw#61E5$5","speech":"noun","speech_id":14,"comments":"","inflections":[{"id":115,"name":"instrumental"}]},{"id":3247,"gloss_id":null,"type":31,"fragment":"!","tengwar":"\u00c1","speech":null,"speech_id":null,"comments":null,"inflections":[]}]`)
    );
    const LatinMap: ITextTransformation = JSON.parse(`{"10":[[0]," ",[1]," ",[2]," ",[3]," ",[4],[5]]}`);
    const TengwarMap: ITextTransformation = JSON.parse(`{"10":[[0,"\`C"]," ",[1,"1~M7T5"]," ",[2,"1U7Ew#6"]," ",[3,"1U7~M5"]," ",[4,"\`Cw#61E5$5"]," ",[5,"\u00c1"]]}`);

    it('supports simple map without substitutions', () => {
        const map = convert('latin', LatinMap, Fragments);

        expect(map.paragraphs.length).to.equal(1);
        expect(map.paragraphs[0].length).to.equal(10);
        
        const expectedIds = [
            Fragments[0].id,
            MinimumId,
            Fragments[1].id,
            MinimumId + 1,
            Fragments[2].id,
            MinimumId + 2,
            Fragments[3].id,
            MinimumId + 3,
            Fragments[4].id,
            MinimumId + 4,
        ];
        expect(map.paragraphs[0].map((v) => v.id)).to.deep.equal(expectedIds);

        const expectedFragments = [
            Fragments[0].fragment,
            ' ',
            Fragments[1].fragment,
            ' ',
            Fragments[2].fragment,
            ' ',
            Fragments[3].fragment,
            ' ',
            Fragments[4].fragment,
            Fragments[5].fragment,
        ];
        expect(map.paragraphs[0].map((v) => v.fragment)).to.deep.equal(expectedFragments);
    });

    it('supports map with substitutions', () => {
        const map = convert('tengwar', TengwarMap, Fragments);

        expect(map.paragraphs.length).to.equal(1);
        expect(map.paragraphs[0].length).to.equal(11);
        
        const expectedIds = [
            Fragments[0].id,
            MinimumId,
            Fragments[1].id,
            MinimumId + 1,
            Fragments[2].id,
            MinimumId + 2,
            Fragments[3].id,
            MinimumId + 3,
            Fragments[4].id,
            MinimumId + 4,
            MinimumId + 5,
        ];
        expect(map.paragraphs[0].map((v) => v.id)).to.deep.equal(expectedIds);

        const expectedFragments = [
            '\`C',
            ' ',
            '1~M7T5',
            ' ',
            '1U7Ew#6',
            ' ',
            '1U7~M5',
            ' ',
            '\`Cw#61E5$5',
            ' ',
            '\u00c1'
        ];
        expect(map.paragraphs[0].map((v) => v.fragment)).to.deep.equal(expectedFragments);
    });
});