import type { IWordListDetail } from '@root/connectors/backend/IWordListApi';

export interface IProps {
    wordList: IWordListDetail;

    /** Whether the viewer owns the list, and may therefore change its visibility. */
    canEdit: boolean;

    /** Raised once the visibility has been changed and persisted. */
    onVisibilityChange: (isPublic: boolean) => void;
}
