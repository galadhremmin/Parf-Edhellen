import { useState, useCallback } from 'react';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import Panel from '@root/components/Panel';
import FailedJobsList from '../components/FailedJobsList';
import ErrorsByWeekBarGraph from '../components/Graph';
import LogList from '../components/LogList';
import type { IProps } from '../index._types';

function Log(props: IProps) {
    const {
        errorsByWeek,
        errorCategories,
        failedJobsByWeek,
        failedJobsCategories,
        logApi,
    } = props;

    const [selectedCategory, setSelectedCategory] = useState<string | undefined>(undefined);
    const [selectedWeek, setSelectedWeek] = useState<string | undefined>(undefined);
    const [selectedYear, setSelectedYear] = useState<number | undefined>(undefined);
    const [selectedWeekNumber, setSelectedWeekNumber] = useState<number | undefined>(undefined);

    const handleCategoryClick = useCallback((category: string, week: string, year?: number, weekNumber?: number) => {
        setSelectedCategory(category);
        setSelectedWeek(week);
        setSelectedYear(year);
        setSelectedWeekNumber(weekNumber);
    }, []);

    const handleClearCategory = useCallback(() => {
        setSelectedCategory(undefined);
        setSelectedWeek(undefined);
        setSelectedYear(undefined);
        setSelectedWeekNumber(undefined);
    }, []);

    const handleCategoryDeleted = useCallback(() => {
        setSelectedCategory(undefined);
        setSelectedWeek(undefined);
        setSelectedYear(undefined);
        setSelectedWeekNumber(undefined);
        // Reload the page to refresh the graph data
        window.location.reload();
    }, []);

    return <>
        <Panel title="Exception log" shadow={true}>
            {errorsByWeek && <section>
                <ErrorsByWeekBarGraph 
                    data={errorsByWeek} 
                    categories={errorCategories}
                    onCategoryClick={handleCategoryClick}
                />
                {selectedCategory && (
                    <div style={{ marginTop: '10px', marginBottom: '10px' }}>
                        <span>Filtering by category: <strong>{selectedCategory}</strong></span>
                        {selectedWeek && <span> in week <strong>{selectedWeek}</strong></span>}
                        {' '}
                        <button
                            className="btn btn-sm btn-secondary float-end"
                            onClick={handleClearCategory}
                            type="button">
                            Clear filter
                        </button>
                    </div>
                )}
            </section>}
            <section>
                <LogList 
                    logApi={logApi} 
                    category={selectedCategory}
                    week={selectedWeek}
                    year={selectedYear}
                    weekNumber={selectedWeekNumber}
                    onCategoryDeleted={handleCategoryDeleted}
                />
            </section>
        </Panel>
        <Panel title="Failed jobs" shadow={true}>
            {failedJobsByWeek && <section>
                <ErrorsByWeekBarGraph data={failedJobsByWeek} categories={failedJobsCategories} />
            </section>}
            <section>
                <FailedJobsList logApi={logApi} />
            </section>
        </Panel>
    </>;
}

export default withPropInjection(Log, {
    logApi: DI.LogApi,
});
