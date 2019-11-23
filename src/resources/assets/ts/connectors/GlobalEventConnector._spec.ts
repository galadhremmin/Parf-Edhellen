/* tslint:disable:no-empty */
import { expect } from 'chai';
import GlobalEventConnector from './GlobalEventConnector';

describe('connectors/GlobalEventConnector', () => {
    let e: GlobalEventConnector;

    beforeEach(() => {
        e = new GlobalEventConnector();
    });

    afterEach(() => {
        e.disconnect();
        e = null;
    });

    it('subscribes to an event', () => {
        e.loadGlossary = () => {};
        expect(e.listeners[e.loadGlossary as unknown as string]).to.exist;
    });

    it('unsubscribes to an event', () => {

        e.loadGlossary = () => {};
        e.loadReference = () => {};
        e.disconnect();

        expect(e.listeners).to.deep.equal({});
    });

    it('fires event', (done) => {
        e.loadGlossary = (ev: CustomEvent) => {
            expect(ev.detail).to.be.null;
            done();
        };

        e.fire(e.loadGlossary);
    });

    it('fires event with details', (done) => {
        const details = {
            x: 1,
        };

        e.loadGlossary = (ev: CustomEvent) => {
            expect(ev.detail).to.deep.equal(details);
            done();
        };

        e.fire(e.loadGlossary, details);
    });
});
