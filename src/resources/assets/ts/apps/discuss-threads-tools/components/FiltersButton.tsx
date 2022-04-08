import classNames from 'classnames';
import queryString from 'query-string';
import React, { useCallback, useState } from 'react';

import { IProps } from './FiltersButton._types';

import './FiltersButton.scss';

const DiscussFilters = [
    {
        id: 'sticky',
        name: 'Pinned threads',
    },
    {
        id: 'default',
        name: 'Regular threads',
    },
    {
        id: 'unanswered',
        name: 'Unanswered threads',
    },
];
const FilterQueryStringKeyName = 'filters';

function FiltersButton(props: IProps) {
    const [ expanded, setExpanded ] = useState(false);
    const [ filterMap, setFilterMap ] = useState(() => {
        // Retrieve the current filter configuration, or default to the filters described by the `DiscussFilters` constant
        const existingFilter = queryString.parse(window.location.search, {
            arrayFormat: 'bracket',
        })[FilterQueryStringKeyName] || DiscussFilters.map((f) => f.id);
        // Build a collection by ensuring that the `existingFilter` is an array.
        const filterCollection: string[] = Array.isArray(existingFilter) ? existingFilter : [existingFilter];
        // Create a map for O(1) loop up as opposed to O(N) for retaining an array.
        return filterCollection.reduce<any>((carry, filterName) => {
            carry[filterName] = true;
            return carry;
        }, {});
    });

    const _onExpand = useCallback(() => {
        setExpanded(! expanded);
    }, [ expanded ]);

    const _onFilterChange = useCallback((ev: React.ChangeEvent) => {
        const {
            value,
        } = ev.target as HTMLInputElement;

        const nextFilterMap = { ...filterMap };
        nextFilterMap[value] = ! nextFilterMap[value];

        setFilterMap(nextFilterMap);
    }, [ filterMap ]);

    const _onApply = useCallback((ev: React.MouseEvent) => {
        const filters = Object.keys(filterMap).filter((f) => filterMap[f]);
        if (filters.length < 1) {
            return;
        }

        window.location.search = queryString.stringify({
            [FilterQueryStringKeyName]: filters,
        }, {
            arrayFormat: 'bracket',
        });
    }, [ filterMap ]);

    return <div className={classNames('btn-group', 'right', 'FiltersButton', { open: expanded })}>
        <button type="button" className="btn btn-secondary" onClick={_onExpand}>
            Filters <span className="caret"></span>
        </button>
        <ul className="dropdown-menu FiltersButton--menu">
            {DiscussFilters.map((f) => <li key={f.id} className="checkbox">
                <label>
                    <input type="checkbox"
                           name="active-filters"
                           value={f.id}
                           checked={filterMap[f.id] || false}
                           onChange={_onFilterChange}
                    />
                    {f.name}
                </label>
            </li>)}
            <li className="text-center">
                <button className="btn btn-primary" onClick={_onApply}>Apply</button>
            </li>
        </ul>
    </div>;
}

export default FiltersButton;
