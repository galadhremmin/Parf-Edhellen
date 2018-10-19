import { expect } from 'chai';
import LazyLoader from './lazy-loader';

describe('utilities/lazy-loader', () => {
    before(() => {
        this.loader = new LazyLoader(() => Promise.resolve(true));
    });

    it('is not alive', () => {
        expect((<LazyLoader<boolean>> this.loader).alive).to.equal(false);
    });

    it('loads', async () => {
        expect(await (<LazyLoader<boolean>> this.loader).get()).to.equal(true);
    });

    it('is cleared', () => {
        const loader = <LazyLoader<boolean>> this.loader;
        loader.clear();
        expect(loader.alive).to.equal(false);
    });
});
