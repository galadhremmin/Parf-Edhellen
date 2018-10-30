import { expect } from 'chai';
import sinon from 'sinon';

import '../utilities/Enzyme';
import ExpiringCache, { IDataWithExpiration, TimeUnit } from './ExpiringCache';
import MemoryStorage from './MemoryStorage';

interface ITestData {
    x: number;
}

describe('utilities/ExpiringCache', () => {
    let sandbox: sinon.SinonSandbox;

    before(() => {
        // ExpiringCache relies on `Date` -- the following causes the Date object to always be 0.
        sandbox = sinon.createSandbox();
    });

    afterEach(() => {
        sandbox.restore();
    });

    it('transforms data from loader to output data', async () => {
        const expectedData = { x: 1 };
        const expectedLifetime = 1000;
        const expectedTimeUnit = TimeUnit.Minutes;
        const expectedPayload: IDataWithExpiration<ITestData> = {
            data: expectedData,
            lifetime: expectedLifetime,
            unit: expectedTimeUnit,
        };
        const expectedKey = 'unit-test';

        sandbox.useFakeTimers(0);

        const store = new MemoryStorage();
        const cache = new ExpiringCache<ITestData>(() => Promise.resolve(expectedPayload), store, expectedKey);

        const actualData = await cache.get();

        expect(actualData).to.deep.equal(expectedData);
        expect(store.getItem(expectedKey)).to.equal(JSON.stringify({
            d: expectedData,
            t: expectedTimeUnit.valueOf() * expectedLifetime,
        }));
    });

    it('expires', async () => {
        const expiredData = { x: 0 };
        const expectedData = { x: 1 };
        const expectedKey = 'unit-test';
        const beginningOfTime = 1000;
        const expiredJson = JSON.stringify({
            d: expiredData,
            t: beginningOfTime,
        });
        const newPayload = {
            data: expectedData,
            lifetime: 1000,
            unit: TimeUnit.Hours,
        };
        const newJson = JSON.stringify({
            d: expectedData,
            t: beginningOfTime + newPayload.lifetime * newPayload.unit,
        });

        sandbox.useFakeTimers(beginningOfTime);

        const store = new MemoryStorage();
        store.setItem(expectedKey, expiredJson);

        const cache = new ExpiringCache<ITestData>(() => Promise.resolve(newPayload), store, expectedKey);
        const data = await cache.get();

        expect(store.getItem(expectedKey)).to.equal(newJson);
        expect(data).to.deep.equal(expectedData);
    });
});
