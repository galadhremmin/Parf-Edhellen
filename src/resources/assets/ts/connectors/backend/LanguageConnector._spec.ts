import { expect } from 'chai';

import { TestCache } from '../../utilities/Cache._spec';
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

describe('connectors/backend/LanguageConnector', () => {
    let languages: LanguageConnector;

    before(() => {
        const cache = new TestCache<any>(() => Promise.resolve(CategorizedLanguages), 'ed.unit-test');
        languages = new LanguageConnector(null, cache);
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
