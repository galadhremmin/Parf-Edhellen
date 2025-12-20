import { useCallback, useMemo } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from 'recharts';

import type { IProps } from './Graph._types';
import { fireEvent } from '@root/components/Component';

const ChartColors = ['#00818a', '#404b69', '#283149', '#6c5b7c', '#c06c84', '#f67280', '#f8b595'];
const MAX_LEGEND_CATEGORIES = 8;
const OTHER_CATEGORY = 'other';

interface IDataEntry {
    [key: string]: string | number | undefined;
    week?: string | number;
    year?: number;
    year_week?: string;
    [OTHER_CATEGORY]?: number;
}

function Graph(props: IProps) {
    const {
        categories,
        data,
        onCategoryClick,
    } = props;

    const { processedData, processedCategories } = useMemo(() => {
        if (!Array.isArray(categories) || !Array.isArray(data) || categories.length <= MAX_LEGEND_CATEGORIES) {
            return { processedData: data as unknown as IDataEntry[], processedCategories: categories };
        }

        // Calculate total for each category across all weeks
        const categoryTotals: { [key: string]: number } = {};
        categories.forEach((category: string) => {
            categoryTotals[category] = 0;
            (data as unknown as IDataEntry[]).forEach((entry: IDataEntry) => {
                const value = entry[category];
                if (value !== undefined && value !== null && typeof value === 'number') {
                    categoryTotals[category] += value;
                }
            });
        });

        // Sort categories by total and take top 8
        const sortedCategories = [...categories].sort((a, b) => {
            return (categoryTotals[b] || 0) - (categoryTotals[a] || 0);
        });
        const topCategories = sortedCategories.slice(0, MAX_LEGEND_CATEGORIES);
        const otherCategories = sortedCategories.slice(MAX_LEGEND_CATEGORIES);

        // Transform data to include "other" category
        const transformedData: IDataEntry[] = (data as unknown as IDataEntry[]).map((entry: IDataEntry) => {
            const newEntry: IDataEntry = { ...entry };
            let otherTotal = 0;
            
            otherCategories.forEach((category: string) => {
                const value = entry[category];
                if (value !== undefined && value !== null && typeof value === 'number') {
                    otherTotal += value;
                }
                delete newEntry[category];
            });
            
            if (otherTotal > 0) {
                newEntry[OTHER_CATEGORY] = otherTotal;
            }
            
            return newEntry;
        });

        const finalCategories = otherCategories.length > 0 
            ? [...topCategories, OTHER_CATEGORY]
            : topCategories;

        return { processedData: transformedData, processedCategories: finalCategories };
    }, [categories, data]);

    const handleCellClick = useCallback((data: IDataEntry, category: string) => {
        if (onCategoryClick && data) {
            // If clicking on "other", we can't determine a specific category, so skip
            if (category === OTHER_CATEGORY) {
                return;
            }
            const week = data.week || data.year_week || '';
            const year = data.year;
            const weekNumber = typeof data.week === 'number' ? data.week : undefined;
            void fireEvent('Graph', onCategoryClick, {
                week: String(week),
                year,
                weekNumber,
                category,
            });
        }
    }, [onCategoryClick]);

    if (! Array.isArray(processedCategories)) {
        return null;
    }

    return <ResponsiveContainer width="100%" aspect={4 / 1.5}>
        <BarChart data={processedData}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="week" />
            <YAxis scale="auto" />
            <Tooltip />
            <Legend />
            {processedCategories.map((category: string, i: number) => <Bar
                key={category}
                dataKey={category}
                fill={ChartColors[i % ChartColors.length]}
                stackId="1">
                {processedData.map((entry: IDataEntry, index: number) => (
                    <Cell
                        key={`cell-${index}`}
                        onClick={() => handleCellClick(entry, category)}
                        style={{ cursor: (onCategoryClick && category !== OTHER_CATEGORY) ? 'pointer' : 'default' }}
                    />
                ))}
            </Bar>)}
        </BarChart>
    </ResponsiveContainer>;
}

export default Graph;
