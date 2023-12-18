import {
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import sinon, { SinonStubbedInstance } from 'sinon';

import ApiConnector from '@root/connectors/ApiConnector';
import { ErrorBoundary } from './ErrorBoundary';

describe('utilities/ErrorBoundary', () => {
    let component: ErrorBoundary;
    let mockedErrorApi: SinonStubbedInstance<ApiConnector>;

    beforeAll(() => {
        mockedErrorApi = sinon.createStubInstance(ApiConnector);
        component = new ErrorBoundary({
            children: [],
            reportErrorApi: mockedErrorApi
        });
    });

    test('excludes unwanted CSS exceptions', () => {
        const error = new Error('Loading CSS chunk 8600 failed.');
        component.componentDidCatch(error, null);
        expect(mockedErrorApi.error.notCalled).toBeTruthy();
    });

    test('excludes unwanted async JavaScript exceptions', () => {
        const error = new Error('ChunkLoadError: Loading chunk 8086 failed.');
        component.componentDidCatch(error, null);
        expect(mockedErrorApi.error.notCalled).toBeTruthy();
    });

    test('saves wanted exceptions', () => {
        const error = new Error('This is a wanted exception');
        component.componentDidCatch(error, null);
        expect(mockedErrorApi.error.calledOnce).toBeTruthy();
    });
});
