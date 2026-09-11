import type { ReactNode } from 'react';

export type ApparatusTab = 'index' | 'entry';

export interface IProps {
    entry: ReactNode;
    index: ReactNode;
    /** What the collapsed sheet says on a narrow screen. */
    handleText: string;
    /**
     * Whether the sheet is open. Controlled by the reader rather than held here: the text
     * has to make room for the sheet, and scrolling a word into view has to know how much
     * of the viewport is left.
     */
    isOpen: boolean;
    onOpenChange: (isOpen: boolean) => void;
    onTabChange: (tab: ApparatusTab) => void;
    tab: ApparatusTab;
}
