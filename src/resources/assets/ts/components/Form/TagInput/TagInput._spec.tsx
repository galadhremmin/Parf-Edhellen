import { fireEvent, render } from '@testing-library/react';
import { act } from 'react-dom/test-utils';
import {
    describe,
    expect,
    test,
} from '@jest/globals';
import TagInput from './TagInput';
import { IComponentEvent } from '@root/components/Component._types';

describe('components/Form', () => {
    describe('TagInput', () => {
        test('mounts', async () => {
            const tags = [
                'A', 'B', 'C', 'D',
            ];

            const wrapper = render(<span>
                <TagInput name="unit-test" value={tags} />
            </span>);

            const tagInputs = await wrapper.findAllByRole('checkbox');
            expect(tagInputs.length).toEqual(tags.length);

            const inputs = await wrapper.findAllByRole('textbox');
            expect(inputs.length).toEqual(1);
        });

        test('can add tags', async () => {
            const original = [ 'S', '3', 'X', 'Y' ];
            const inject = 'i pace';
            const expected = [ '3', inject, 'S', 'X', 'Y' ];

            let actual: string[] = [];
            const onChange = (ev: IComponentEvent<string[]>) => {
                actual = ev.value;
            };

            const wrapper = render(<TagInput name="unit-test" value={original} onChange={onChange} />);
            const input = await wrapper.findByRole('textbox');

            // add a tag to the array of tags.
            fireEvent.change(input, {
                target: {
                    value: inject,
                },
            });
            fireEvent.keyDown(input, {
                key: 'Enter',
            });

            expect(actual).toEqual(expected);
        });

        test('trims tags', async () => {
            const inject = ' tesla ';
            const expected = 'tesla';

            const _onChange = (ev: IComponentEvent<string[]>) => {
                expect(ev.value).toContain(expected);
            };
            const wrapper = render(<TagInput name="unit-test" onChange={_onChange} />);
            const input = await wrapper.findByRole('textbox');

            // add a tag to the array of tags.
            fireEvent.change(input, {
                target: {
                    value: inject,
                },
            });
            fireEvent.keyDown(input, {
                key: 'Enter',
            });
        });
    });
});
