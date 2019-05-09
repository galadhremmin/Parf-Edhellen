import { expect } from 'chai';
import { mount } from 'enzyme';
import React from 'react';

import TagInput from './TagInput';

import '@root/utilities/Enzyme';

describe('components/TagInput', () => {
    it('mounts', () => {
        const tags = [
            'A', 'B', 'C', 'D',
        ];

        const wrapper = mount(<span>
            <TagInput value={tags} />
        </span>);

        expect(wrapper.find('TagLabel').length).to.equal(tags.length);
        expect(wrapper.find('input[type=text]').length).to.equal(1);
    });
});
