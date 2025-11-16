import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import type IRoleManager from '@root/security/IRoleManager';

export interface IProps {
    lexicalEntry: ILexicalEntryEntity;
    toolbar: boolean;
    roleManager?: IRoleManager;
}
