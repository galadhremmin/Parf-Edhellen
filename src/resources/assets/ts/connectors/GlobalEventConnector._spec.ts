/* tslint:disable:no-empty */
import {
    afterAll,
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import GlobalEventConnector from './GlobalEventConnector';

describe('connectors/GlobalEventConnector', () => {
    let e: GlobalEventConnector;

    beforeAll(() => {
        e = new GlobalEventConnector();
    });

    afterAll(() => {
        e.disconnect();
        e = null;
    });

    test('subscribes to an event', () => {
        e.loadGlossary = () => {};
        expect(e.listeners[e.loadGlossary as unknown as string]).toEqual(expect.anything());
    });

    test('unsubscribes to an event', () => {

        e.loadGlossary = () => {};
        e.loadReference = () => {};
        e.disconnect();

        expect(e.listeners).toEqual({});
    });

    test('fires event', (done) => {
        e.loadGlossary = (ev: CustomEvent) => {
            expect(ev.detail).toBeNull();
            done();
        };

        e.fire(e.loadGlossary);
    });

    test('fires event with details', (done) => {
        const details = {
            x: 1,
        };

        e.loadGlossary = (ev: CustomEvent) => {
            expect(ev.detail).toEqual(details);
            done();
        };

        e.fire(e.loadGlossary, details);
    });
});
