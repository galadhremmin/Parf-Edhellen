import classNames from 'classnames';
import React, { useCallback, useState } from 'react';

import './FiltersButton.scss';
import {
    buildQueryString,
    parseQueryString,
    QueryStringValue,
} from '@root/utilities/func/query-string';

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

function FiltersButton() {
    const [ filterMap, setFilterMap ] = useState<Record<string, boolean>>(() => {
        // Retrieve the current filter configuration, or default to the filters described by the `DiscussFilters` constant
        const existingFilter = parseQueryString(window.location.search)[FilterQueryStringKeyName] || DiscussFilters.map((f) => f.id);
        // Build a collection by ensuring that the `existingFilter` is an array.
        const filterCollection: QueryStringValue[] = Array.isArray(existingFilter) ? existingFilter : [existingFilter];
        // Create a map for O(1) loop up as opposed to O(N) for retaining an array.
        return filterCollection.reduce((carry, filterName: string) => {
            carry[filterName] = true;
            return carry;
        }, {} as Record<string, boolean>);
    });

    const [ isOpen, setIsOpen ] = useState<boolean>(false);

    const _onOpenClick = useCallback((ev: React.MouseEvent) => {
        ev.preventDefault();
        setIsOpen((x) => !x);
    }, []);

    const _onFilterChange = useCallback((ev: React.ChangeEvent) => {
        const {
            value,
        } = ev.target as HTMLInputElement;

        const nextFilterMap = { ...filterMap };
        nextFilterMap[value] = ! nextFilterMap[value];

        setFilterMap(nextFilterMap);
    }, [ filterMap ]);

    const _onApply = useCallback((ev: React.MouseEvent) => {
        ev.preventDefault();

        const filters = Object.keys(filterMap).filter((f) => filterMap[f]);
        if (filters.length < 1) {
            return;
        }

        window.location.search = buildQueryString({
            [FilterQueryStringKeyName]: filters,
        });
    }, [ filterMap ]);

    return <div className={classNames('dropdown', 'right', 'FiltersButton')}>
        <button type="button" className="btn btn-secondary dropdown-toggle" onClick={_onOpenClick}>
            Filters <span className="caret"></span>
        </button>
        <ul className={classNames('dropdown-menu', 'FiltersButton--menu', { show: isOpen })}>
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
