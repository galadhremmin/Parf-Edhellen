import {
    describe,
    expect,
    test,
} from '@jest/globals';

import BrowserHistory from './BrowserHistory';

describe('utilities/BrowserHistory', () => {
    test('supports query string', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).toEqual('?hello=world&name=arda');
            },
        });

        history.push({
            queryString: {
                hello: 'world',
                name: 'arda',
            },
        });
    });

    test('supports path property', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).toEqual('/hello/world');
            },
        });

        history.push({
            path: '/hello/world',
        });
    });

    test('supports path and query string property', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).toEqual('/hello/world?hello=world&name=arda');
            },
        });

        history.push({
            path: '/hello/world',
            queryString: {
                hello: 'world',
                name: 'arda',
            },
        });
    });
});
