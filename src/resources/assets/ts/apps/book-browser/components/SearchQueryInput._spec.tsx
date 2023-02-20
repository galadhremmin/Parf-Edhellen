import { fireEvent, render, screen } from '@testing-library/react';
import { act } from 'react-dom/test-utils';
import sinon from 'sinon';
import {
    describe,
    expect,
    test,
} from '@jest/globals';

import SearchQueryInput from './SearchQueryInput';

describe('apps/book-browser/components/SearchQueryInput', () => {
    test('is mounted', () => {
        const noop = (ev: any) => { expect(ev).toBe(expect.anything()); };
        const wrapper = render(<SearchQueryInput name="unit-test" value={''} onChange={noop} />);
        expect(wrapper.container).not.toBeNull();
    });

    test('will propagate props', async () => {
        const expectedProps = {
            name: 'unit-test-name',
            value: 'a value',
        };

        render(<SearchQueryInput {...expectedProps} />);

        const input = await screen.findAllByRole('searchbox');
        for (const prop of Object.keys(expectedProps)) {
            expect(input[0].getAttribute(prop)).toEqual((expectedProps as any)[prop]);
        }
    });

    test('will notify on change', () => {
        const expectedValue = 'this is a new value which will trigger `onChange`.';
        const expectedChangeArguments = {
            name: "unit-test",
            value: expectedValue,
        };
        const changeStub = sinon.stub();

        render(<SearchQueryInput name="unit-test" value="" onChange={changeStub} />);

        act(() => {
            fireEvent.change(
                screen.getByRole('searchbox'), {
                    target: {
                        value: expectedValue,
                    },
                }
            );
        });

        expect(changeStub.callCount).toEqual(1);
        expect(changeStub.firstCall.args[0]).toEqual(expectedChangeArguments);
    });
});
