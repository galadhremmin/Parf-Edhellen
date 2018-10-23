import { expect } from 'chai';

import { TestCache } from '../../utilities/Cache.spec';
import LanguageConnector from './LanguageConnector';

const CategorizedLanguages = {
    category: [
        { id: 1, name: 'language 1' },
        { id: 2, name: 'language 2' },
        { id: 3, name: 'language 3' },
    ],
    category2: [
        { id: 4, name: 'language 4' },
        { id: 5, name: 'language 5' },
    ],
};

describe('connectors/LanguageConnector', () => {
    let languages: LanguageConnector;

    before(() => {
        languages = new LanguageConnector({
            languages: () => Promise.resolve(CategorizedLanguages),
        } as any, new TestCache(null, 'ed.unit-test'));
    });

    it('returns categoried languages', async () => {
        const all = await languages.all();
        expect(all).to.equal(CategorizedLanguages);
    });

    it('finds a specific language', async () => {
        const expected = CategorizedLanguages.category[0];
        const language = await languages.find(expected.name, 'name');
        expect(language).to.equal(expected);
    });

    it('cannot find a language', async () => {
        const expected: any = null;
        const language = await languages.find('bogus', 'name');
        expect(language).to.equal(expected);
    });
});
