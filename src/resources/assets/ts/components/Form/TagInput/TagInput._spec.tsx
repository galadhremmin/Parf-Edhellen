import { expect } from 'chai';
import React from 'react';

import { IComponentEvent } from '../../Component._types';
import TagInput from './TagInput';

/*
describe('components/Form', () => {
    describe('TagInput', () => {
        it('mounts', () => {
            const tags = [
                'A', 'B', 'C', 'D',
            ];

            const wrapper = mount(<span>
                <TagInput name="unit-test" value={tags} />
            </span>);

            expect(wrapper.find('TagLabel').length).to.equal(tags.length);
            expect(wrapper.find('input.form-control').length).to.equal(1);
            expect(wrapper.find('input.form-control').prop('type')).to.equal('text');
        });

        it('can add tags', () => {
            const original = [ 'S', '3', 'X', 'Y' ];
            let actual: string[] = [];
            const inject = 'i pace';
            const expected = [ '3', inject, 'S', 'X', 'Y' ];

            const _onChange = (ev: IComponentEvent<string[]>) => {
                expect(ev.value).to.contain(inject);
                actual = ev.value;
            };
            const wrapper = mount(<TagInput name="unit-test" value={original} onChange={_onChange} />);
            const input = wrapper.find('input.form-control');

            // add a tag to the array of tags.
            input.simulate('change', { target: { value: inject } });
            input.simulate('keypress', { key: 'Enter' });
            expect(actual).to.deep.equal(expected);
        });

        it('trims tags', (done) => {
            const inject = ' tesla ';
            const expected = 'tesla';

            const _onChange = (ev: IComponentEvent<string[]>) => {
                expect(ev.value).to.contain(expected);
                done();
            };
            const wrapper = mount(<TagInput name="unit-test" onChange={_onChange} />);
            const input = wrapper.find('input.form-control');

            // add a tag to the array of tags.
            input.simulate('change', { target: { value: inject } });
            input.simulate('keypress', { key: 'Enter' });
        });

        it('can delete tags', () => {
            const original = [ 'S', '3', 'X', 'Y', 'i pace' ];
            let actual: string[] = [];
            const remove = original[original.length - 1];

            const _onChange = (ev: IComponentEvent<string[]>) => {
                expect(ev.value).to.not.contain(remove);
                actual = ev.value;
            };
            const wrapper = mount(<TagInput name="unit-test" value={original} onChange={_onChange} />);
            const input = wrapper.find(`input[name="tag-checkbox--${remove}"]`);

            // remove the tag by unchecking it
            input.simulate('change', { target: { checked: false } });

            const expected = original.filter((e: string) => e !== remove);
            expect(actual).to.deep.equal(expected);
        });
    });
});
*/
