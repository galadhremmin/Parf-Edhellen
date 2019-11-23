import { expect } from 'chai';
import { mount, ReactWrapper } from 'enzyme';
import React from 'react';

import { IComponentEvent } from '../../Component._types';
import AsyncSelect from './AsyncSelect';

import '@root/utilities/Enzyme';

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

        it('mounts', (done) => {
            const wrapper = mount(<AsyncSelect loaderOfValues={DefaultLoader} textField="t" valueField="x" />);

            setTimeout(() => {
                wrapper.update();

                const options = wrapper.find('option');
                expect(options.length).to.equal(Values.length);

                for (let i = 0; i < Values.length; i += 1) {
                    expect(options.at(i).prop('value')).to.equal(Values[i].x);
                    expect(options.at(i).text()).to.equal(Values[i].t);
                }

                done();
            });
        });

        it('supports id as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).to.equal(value.x);
                done();
            };

            const wrapper = mount(<AsyncSelect
                loaderOfValues={DefaultLoader}
                name="unit-test"
                onChange={onChange}
                textField="t"
                valueField="x"
                valueType="id"
            />);

            setTimeout(() => {
                wrapper.update();
                wrapper.find(`select`).simulate('change', { target: { value: value.x } });
            });
        });

        it('supports entity as value output', (done) => {
            const value = Values[Math.ceil(Values.length / 2)];

            const onChange = (ev: IComponentEvent<any>) => {
                expect(ev.value).to.deep.equal(value);
                done();
            };

            const wrapper = mount(<AsyncSelect
                loaderOfValues={DefaultLoader}
                name="unit-test"
                onChange={onChange}
                textField="t"
                valueField="x"
                valueType="entity"
            />);

            setTimeout(() => {
                wrapper.update();
                wrapper.find(`select`).simulate('change', { target: { value: value.x } });
            });
        });
    });
});
