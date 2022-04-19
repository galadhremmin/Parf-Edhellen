import { expect } from 'chai';
import sinon from 'sinon';

import { ApplicationGlobalPrefix } from '@root/config';
import ExpiringCache, { IDataWithExpiration, TimeUnit } from './ExpiringCache';
import MemoryStorage from './MemoryStorage';

import '../utilities/Enzyme';

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
        const expectedLifetime = 1;
        const expectedTimeUnit = TimeUnit.Hours;
        const expectedKey = `${ApplicationGlobalPrefix}.unit-test`;

        sandbox.useFakeTimers(0);

        const store = new MemoryStorage();
        const cache = new ExpiringCache<ITestData>(() => Promise.resolve(expectedData), store, expectedKey);

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
        const expectedKey = `${ApplicationGlobalPrefix}.unit-test`;
        const beginningOfTime = 1000;
        const expiredJson = JSON.stringify({
            d: expiredData,
            t: beginningOfTime,
        });
        const expectedLifetime = 1000;
        const expectedUnit = TimeUnit.Days;
        const newJson = JSON.stringify({
            d: expectedData,
            t: beginningOfTime + expectedLifetime * expectedUnit,
        });

        sandbox.useFakeTimers(beginningOfTime);

        const store = new MemoryStorage();
        store.setItem(expectedKey, expiredJson);

        const cache = new ExpiringCache<ITestData>(() => Promise.resolve(expectedData), store, expectedKey,
            expectedLifetime, expectedUnit);
        const data = await cache.get();

        expect(store.getItem(expectedKey)).to.equal(newJson);
        expect(data).to.deep.equal(expectedData);
    });
});
