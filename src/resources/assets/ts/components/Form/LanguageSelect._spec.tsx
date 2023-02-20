import {
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import sinon from 'sinon';

import { ILanguagesResponse } from '@root/connectors/backend/IBookApi';
import LanguageConnector from '@root/connectors/backend/LanguageConnector';
import {
    LanguageSelect,
    LanguageWithWritingModeOnlyFilter,
} from './LanguageSelect';
import { render } from '@testing-library/react';

describe('components/Form', () => {
    describe('LanguageSelect', () => {
        const languages: ILanguagesResponse = JSON.parse(
            // tslint:disable-next-line: max-line-length
            `{"Late Period (1950-1973)":[{"id":10,"isInvented":1,"isUnusual":0,"name":"Adûnaic","shortName":"ad","tengwar":null,"tengwarMode":"adunaic"},{"id":9,"name":"Black Speech","isInvented":1,"tengwar":null,"tengwarMode":"blackspeech","isUnusual":0,"shortName":"bs"},{"id":2,"name":"Quenya","isInvented":1,"tengwar":"zR5Ì#","tengwarMode":"quenya","isUnusual":0,"shortName":"q"},{"id":1,"name":"Sindarin","isInvented":1,"tengwar":"iT2#7T5","tengwarMode":"sindarin","isUnusual":0,"shortName":"s"}],"Middle Period (1930-1950)":[{"id":90,"name":"Doriathrin","isInvented":1,"tengwar":null,"tengwarMode":"sindarin-beleriand","isUnusual":1,"shortName":"ilk"},{"id":98,"name":"Lemberin","isInvented":1,"tengwar":null,"tengwarMode":null,"isUnusual":1,"shortName":"lem"},{"id":4,"name":"Noldorin","isInvented":1,"tengwar":"5^mY7T5","tengwarMode":"sindarin-beleriand","isUnusual":0,"shortName":"n"}],"Early Period (1910-1930)":[{"id":91,"name":"Gnomish","isInvented":1,"tengwar":null,"tengwarMode":null,"isUnusual":1,"shortName":"g"},{"id":100,"name":"Solosimpi","isInvented":1,"tengwar":null,"tengwarMode":null,"isUnusual":1,"shortName":"et"}]}`,
        );
        let languageConnector: sinon.SinonStubbedInstance<LanguageConnector>;

        beforeAll(() => {
            languageConnector = sinon.createStubInstance(LanguageConnector);
            languageConnector.all.returns(Promise.resolve(languages));
        });

        test('includes all languages by default', async () => {
            const result = render(<LanguageSelect languageConnector={languageConnector as any} />);
            
            const groups = await result.findAllByRole('group');
            const options = await result.findAllByRole('option');

            expect(groups.length).toEqual(Object.keys(languages).length);
            expect(options.length).toEqual(
                // starting from 1 here because the component will always have a "select a language" alternative.
                Object.keys(languages).reduce((carry, group) => carry + languages[group].length, 1),
            );
        });

        test('includes filters languages with writing modes', async () => {
            const wrapper = render(<LanguageSelect languageConnector={languageConnector as any}
                filter={LanguageWithWritingModeOnlyFilter} />);

            const options = await wrapper.findAllByRole('option');
            expect(options.length).toEqual(
                // starting from 1 here because the component will always have a "select a language" alternative.
                Object.keys(languages).reduce(
                    (carry, group) => carry + languages[group].filter((v) => !! v.tengwarMode).length, 1),
            );
        });

        test('supports formatting', async () => {
            const wrapper = render(<LanguageSelect languageConnector={languageConnector as any}
                formatter={(l) => String(l.id)} />);

            const options = await wrapper.findAllByRole('option');
            expect(options.map((v) => v.textContent)).toEqual(
                Object.keys(languages).reduce(
                    (carry, group) => carry.concat(languages[group].map((v) => String(v.id))), [ 'All languages' ]),
            );
        });
    });
});
