import {
    beforeAll,
    describe,
    expect,
    test
} from '@jest/globals';
import { resolve } from '.';
import setupContainer from './config';
import { DI } from './keys';

describe('di/config', () => {
    beforeAll(() => {
        setupContainer();
    });

    test('The DI container is completely configured', () => {
        const requiredKeys = [
            DI.AccountApi,
            DI.BackendApi,
            DI.BookApi,
            DI.BrowserHistory,
            DI.ContributionApi,
            DI.DiscussApi,
            DI.Glaemscribe,
            DI.GlossApi,
            DI.InflectionApi,
            DI.LanguageApi,
            DI.LogApi,
            DI.RoleManager,
            DI.SpeechApi,
            DI.SubscriptionApi,
            DI.UtilityApi,
            DI.WordFinderApi,
        ];

        for (const key of requiredKeys) {
            expect(resolve(key)).toBeDefined();
        }
    });
});
