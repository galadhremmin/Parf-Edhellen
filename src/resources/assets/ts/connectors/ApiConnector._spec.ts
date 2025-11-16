import {
    afterEach,
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import * as sinon from 'sinon';

import ApiConnector from './ApiConnector';
import { ErrorCategory } from './IReportErrorApi';

describe('connectors/ApiConnector', () => {
    
    /**
     * Simulates a headers object specifically for testing features that rely on fetch.
     * 
     * @param h - The headers object.
     * @returns A headers object.
     */
    function _makeHeaders(h: Record<string, string>) {
        const lower: Record<string, string> = {};
        Object.keys(h || {}).forEach(k => lower[k.toLowerCase()] = h[k]);
        return {
            get: (k: string) => lower[k.toLowerCase()] ?? null,
            entries: () => Object.entries(lower)[Symbol.iterator](),
        } as any;
    }

    /**
     * Simulates a JSON response object specifically for testing features that rely on fetch.
     * 
     * @param body - The body of the response.
     * @param status - The status code of the response.
     * @param headers - The headers of the response.
     * @returns A response object.
     */
    function _makeJsonResponse(body: any, status = 200, headers: Record<string, string> = { 'content-type': 'application/json' }) {
        return {
            ok: status >= 200 && status < 300,
            status,
            headers: _makeHeaders(headers),
            json: async () => body,
            text: async () => JSON.stringify(body),
        } as any;
    }

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
    let fetchStub: sinon.SinonStub;

    beforeAll(() => {
        sandbox = sinon.createSandbox();
        api = new ApiConnector(ApiPrefix, ApiErrorMethod, 421);
        // Ensure fetch exists so sinon can stub it
        if (!(global as any).fetch) {
            (global as any).fetch = (() => Promise.resolve(_makeJsonResponse({}, 200))) as any;
        }
    });

    afterEach(() => {
        if (fetchStub) {
            fetchStub.restore();
        }
        sandbox.restore();
    });

    test('supports DELETE, HEAD, GET', async () => {
        const verbs = ['delete', 'head', 'get'];

        for (const verb of verbs) {
            fetchStub = sandbox.stub(global as any, 'fetch')
                .callsFake((input: RequestInfo | URL, init?: RequestInit) => {
                    expect(input).toEqual(`${ApiPrefix}/${ApiMethod}`);
                    expect(init?.method).toEqual(verb.toUpperCase());
                    return Promise.resolve(_makeJsonResponse(ApiResponse.data, 200));
                });

            const result = await (api as any)[verb](ApiMethod);
            expect(result).toEqual(ApiResponse.data);
            fetchStub.restore();
        }
    });

    test('supports POST, PUT', async () => {
        const verbs = ['post', 'put'];

        for (const verb of verbs) {
            fetchStub = sandbox.stub(global as any, 'fetch')
                .callsFake((input: RequestInfo | URL, init?: RequestInit) => {
                    expect(input).toEqual(`${ApiPrefix}/${ApiMethod}`);
                    expect(init?.method).toEqual(verb.toUpperCase());
                    // Body should be JSON string with snake_cased payload
                    const parsed = JSON.parse(String(init?.body));
                    expect(parsed).toEqual({ complex: { x: 1 } });
                    return Promise.resolve(_makeJsonResponse(ApiResponse.data, 200));
                });

            const result = await (api as any)[verb](ApiMethod, ApiPayload);
            expect(result).toEqual(ApiResponse.data);
            fetchStub.restore();
        }
    });

    test('can handle errors', () => {
        // First fetch simulates failing GET
        const failingFetch = sinon.stub().callsFake((input: RequestInfo | URL, init?: RequestInit) => {
            if (String(input).endsWith(ApiMethod)) {
                return Promise.resolve(_makeJsonResponse({ dummy: true }, 500, { 'X-Caused-By': 'unit-test', 'content-type': 'application/json' }));
            }
            // Error reporting call
            return Promise.resolve(_makeJsonResponse(ApiResponse.data, 200));
        });
        fetchStub = sandbox.stub(global as any, 'fetch').callsFake(failingFetch as any);

        return api.get(ApiMethod).catch(() => true);
    });

    test('can report errors', async () => {
        const message = 'something went wrong';
        const url = 'http://localhost/unit-tests';
        const error = 'stacktrace missing';
        const category: ErrorCategory = ErrorCategory.UnitTest;

        fetchStub = sandbox.stub(global as any, 'fetch')
            .callsFake((input: RequestInfo | URL, init?: RequestInit) => {
                expect(input).toEqual(`${ApiPrefix}/${ApiErrorMethod}`);
                const body = JSON.parse(String(init?.body));
                expect(body).toEqual({ message, url, error, category, duration: null });
                return Promise.resolve(_makeJsonResponse(ApiResponse.data, 200));
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

        fetchStub = sandbox.stub(global as any, 'fetch')
            .callsFake((input: RequestInfo | URL) => {
                expect(input).toEqual(`${ApiPrefix}/${ApiMethod}${expectedQueryString}`);
                done();
                return Promise.resolve(_makeJsonResponse(ApiResponse.data, 200));
            });

        api.get(ApiMethod, queryStringMap);
    });
});
