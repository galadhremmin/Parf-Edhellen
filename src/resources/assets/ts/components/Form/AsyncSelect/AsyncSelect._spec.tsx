import {
    describe,
    expect,
    test,
} from '@jest/globals';
import { fireEvent, render, screen } from '@testing-library/react';
import { IComponentEvent } from '../../Component._types';
import AsyncSelect from './AsyncSelect';

describe('components/Form', () => {
    describe('AsyncSelect', () => {
        const Values = [
            { x : 1, t: 'V' },
            { x : 2, t: 'W' },
            { x : 3, t: 'X' },
            { x : 4, t: 'Y' },
            { x : 5, t: 'Z' },
        ];

        const DefaultLoader = () => Promise.resolve(Values);

        test('mounts', async () => {
            render(<AsyncSelect loaderOfValues={DefaultLoader} textField="t" valueField="x" valueType="entity" />);

            const options = await screen.findAllByRole<HTMLOptionElement>('option');
            expect(options.length).toEqual(Values.length);

            options.forEach((option, i) => {
                expect(option.value).toEqual(Values[i].x.toString(10));
                expect(option.textContent).toEqual(Values[i].t);
            });
        });

        test('supports id as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).toEqual(value.x);
                done();
            };

            const result = render(<AsyncSelect
                loaderOfValues={DefaultLoader}
                name="unit-test"
                onChange={onChange}
                textField="t"
                valueField="x"
                valueType="id"
            />);

            result.findAllByRole('option').then((options) => {
                fireEvent.change(options[0].parentElement, {
                    target: {
                        value: value.x,
                    }
                });
            });
        });

        test('supports entity as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).toEqual(value);
                done();
            };

            const result = render(<AsyncSelect
                loaderOfValues={DefaultLoader}
                name="unit-test"
                onChange={onChange}
                textField="t"
                valueField="x"
                valueType="entity"
            />);

            result.findAllByRole('option').then((options) => {
                fireEvent.change(options[0].parentElement, {
                    target: {
                        value: value.x,
                    }
                });
            });
        });
    });
});
