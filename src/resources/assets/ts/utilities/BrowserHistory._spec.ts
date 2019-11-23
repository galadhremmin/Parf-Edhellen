import { expect } from 'chai';

import BrowserHistory from './BrowserHistory';

describe('utilities/BrowserHistory', () => {
    it('supports query string', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).to.equal('?hello=world&name=arda');
            },
        });

        history.push({
            queryString: {
                hello: 'world',
                name: 'arda',
            },
        });
    });

    it('supports path property', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).to.equal('/hello/world');
            },
        });

        history.push({
            path: '/hello/world',
        });
    });

    it('supports path and query string property', () => {
        const history = new BrowserHistory({
            pushState: (...args) => {
                expect(args[2]).to.equal('/hello/world?hello=world&name=arda');
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
