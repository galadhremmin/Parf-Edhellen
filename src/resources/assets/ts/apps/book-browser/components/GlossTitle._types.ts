import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';
import IRoleManager from '@root/security/IRoleManager';

export interface IProps {
    gloss: IBookGlossEntity;
    toolbar: boolean;
    roleManager?: IRoleManager;
}
