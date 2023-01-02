import axios from 'axios';
import {
    afterEach,
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import * as sinon from 'sinon';

import ApiConnector from './ApiConnector';

describe('connectors/ApiConnector', () => {
    const ApiPrefix = '/api/unit-test';
    const ApiMethod = 'test/now';
    const ApiErrorMethod = 'error';
    const ApiPayload = {
        complex: {
            x: 1,
        },
    };
    const ApiResponse = {
        data: true,
    };

    let sandbox: sinon.SinonSandbox;
    let api: ApiConnector;

    beforeAll(() => {
        sandbox = sinon.createSandbox();
        api = new ApiConnector(ApiPrefix, ApiErrorMethod, 421);
    });

    afterEach(() => {
        sandbox.restore();
    });

    test('supports DELETE, HEAD, GET', async () => {
        const verbs = ['delete', 'head', 'get'];

        for (const verb of verbs) {
            sandbox.stub(axios, verb as any)
                .callsFake((path, config) => {
                    expect(path).toEqual(`${ApiPrefix}/${ApiMethod}`);
                    expect(config).toEqual(api.config);
                    return Promise.resolve(ApiResponse);
                });

            const result = await (api as any)[verb](ApiMethod);
            expect(result).toEqual(ApiResponse.data);
        }
    });

    test('supports POST, PUT', async () => {
        const verbs = ['post', 'put'];

        for (const verb of verbs) {
            sandbox.stub(axios, verb as any)
                .callsFake((path, payload, config) => {
                    expect(path).toEqual(`${ApiPrefix}/${ApiMethod}`);
                    expect(payload).toEqual(ApiPayload);
                    expect(config).toEqual(api.config);

                    return Promise.resolve(ApiResponse);
                });

            const result = await (api as any)[verb](ApiMethod, ApiPayload);
            expect(result).toEqual(ApiResponse.data);
        }
    });

    test('can handle errors', () => {
        const faultyResponse = {
            response: {
                data: {
                    dummy: true,
                },
                headers: {
                    'X-Caused-By': 'unit-test',
                },
                status: 500,
            },
        };

        sandbox.stub(axios, 'get')
            .callsFake(() => {
                return Promise.reject(faultyResponse);
            });

        sandbox.stub(axios, 'post')
            .callsFake((method, payload: any) => {
                expect(method).toEqual(`${ApiPrefix}/${ApiErrorMethod}`);
                expect(payload.category).toEqual('frontend');
                expect(payload.url).toEqual(ApiMethod);
                expect(typeof payload.error).toEqual('string');

                return Promise.resolve(ApiResponse) as Promise<any>;
            });

        api.get(ApiMethod)
            .catch(() => true); // silence exception
    });

    test('can report errors', async () => {
        const message = 'something went wrong';
        const url = 'http://localhost/unit-tests';
        const error = 'stacktrace missing';
        const category = 'unit-tests';

        sandbox.stub(axios, 'post')
            .callsFake((method, payload) => {
                expect(method).toEqual(`${ApiPrefix}/${ApiErrorMethod}`);
                expect(payload).toEqual({ message, url, error, category });
                return Promise.resolve(ApiResponse) as Promise<any>;
            });

        const result = await api.error(message, url, error, category);
        expect(result).toEqual(ApiResponse.data);
    });

    test('supports query strings', (done) => {
        const queryStringMap = {
            n: 'hello world',
            x: 1,
            y: 2,
            z: 3,
            zyxXel: 1500,
        };
        const expectedQueryString = '?n=hello%20world&x=1&y=2&z=3&zyx_xel=1500';

        const faker: any = (path: string) => {
            expect(path).toEqual(`${ApiPrefix}/${ApiMethod}${expectedQueryString}`);
            done();
        };
        sandbox.stub(axios, 'get')
            .callsFake(faker);

        api.get(ApiMethod, queryStringMap);
    });
});
