import { expect } from 'chai';
import {
    shallow,
    ShallowWrapper,
} from 'enzyme';
import React from 'react';
import sinon, { SinonStubbedInstance } from 'sinon';

import ApiConnector from '@root/connectors/ApiConnector';
import ErrorBoundary from './ErrorBoundary';

import './Enzyme';

describe('utilities/ErrorBoundary', () => {
    let component: ShallowWrapper<ErrorBoundary>;
    let mockedErrorApi: SinonStubbedInstance<ApiConnector>;

    beforeEach(() => {
        mockedErrorApi = sinon.createStubInstance(ApiConnector);
        component = shallow(<ErrorBoundary reportErrorApi={mockedErrorApi as never}>nothing</ErrorBoundary>);
    });

    it('excludes unwanted CSS exceptions', () => {
        const error = new Error('Loading CSS chunk 8600 failed.');
        component.instance().componentDidCatch(error, null);
        expect(mockedErrorApi.error.notCalled).to.be.true;
    });

    it('excludes unwanted async JavaScript exceptions', () => {
        const error = new Error('Loading chunk 8600 failed.');
        component.instance().componentDidCatch(error, null);
        expect(mockedErrorApi.error.notCalled).to.be.true;
    });

    it('saves wanted exceptions', () => {
        const error = new Error('This is a wanted exception');
        component.instance().componentDidCatch(error, null);
        expect(mockedErrorApi.error.calledOnce).to.be.true;
    });
});
