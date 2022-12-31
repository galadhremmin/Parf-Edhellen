import { fireEvent, render, RenderResult, screen } from '@testing-library/react';
import { expect } from 'chai';
import React from 'react';

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

        it('mounts', async () => {
            render(<AsyncSelect loaderOfValues={DefaultLoader} textField="t" valueField="x" />);

            const options = await screen.findAllByRole<HTMLOptionElement>('option');
            expect(options.length).to.equal(Values.length);

            options.forEach((option, i) => {
                expect(option.value).to.equal(Values[i].x.toString(10));
                expect(option.textContent).to.equal(Values[i].t);
            });
        });

        it('supports id as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).to.equal(value.x);
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

        it('supports entity as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).to.deep.equal(value);
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
