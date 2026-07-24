import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import type IRoleManager from '@root/security/IRoleManager';

export interface IProps {
    lexicalEntry: ILexicalEntryEntity;
    onPromoteFeatured?: () => void;
    toolbar: boolean;
    roleManager?: IRoleManager;
}
