import {
    afterEach,
    describe,
    expect,
    jest,
    test,
} from '@jest/globals';
import { fireEvent, render, screen } from '@testing-library/react';
import sinon from 'sinon';

import { act } from 'react';
import { DefaultPlaceholder } from './SearchQueryInput._suggestions';
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
            void fireEvent.change(
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

    describe('idle animation', () => {
        afterEach(() => {
            jest.useRealTimers();
        });

        test('will type out a suggestion while the field is empty', () => {
            jest.useFakeTimers();

            render(<SearchQueryInput name="unit-test" value="" />);

            const input = screen.getByRole('searchbox');
            expect(input.getAttribute('placeholder')).toEqual(DefaultPlaceholder);

            act(() => {
                jest.advanceTimersByTime(10000);
            });

            expect(input.getAttribute('placeholder')).toMatch(/^Try “.+” — .+$/);
        });

        test('will look up the suggestion it is advertising', () => {
            jest.useFakeTimers();
            const selectStub = sinon.stub();

            render(<SearchQueryInput name="unit-test" value="" onSuggestionSelect={selectStub} />);

            act(() => {
                jest.advanceTimersByTime(10000);
            });

            const placeholder = screen.getByRole('searchbox').getAttribute('placeholder');

            act(() => {
                void fireEvent.click(screen.getByRole('button', { name: 'Try it' }));
            });

            expect(selectStub.callCount).toEqual(1);
            expect(placeholder).toContain(`“${selectStub.firstCall.args[0].value as string}”`);
        });

        test('will not offer a suggestion without a handler for it', () => {
            jest.useFakeTimers();

            render(<SearchQueryInput name="unit-test" value="" />);

            act(() => {
                jest.advanceTimersByTime(10000);
            });

            expect(screen.queryByRole('button')).toBeNull();
        });

        test('will not cycle the placeholder while the field has a value', () => {
            jest.useFakeTimers();

            render(<SearchQueryInput name="unit-test" value="mellon" />);

            act(() => {
                jest.advanceTimersByTime(10000);
            });

            expect(screen.getByRole('searchbox').getAttribute('placeholder')).toEqual(DefaultPlaceholder);
        });
    });
});
