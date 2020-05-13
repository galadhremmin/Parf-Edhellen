import { IComponentProps } from '../FormComponent._types';

type FilterFlags<Base, Condition> = {
    [Key in keyof Base]: Base[Key] extends Condition ? Key : never;
};

type AllowedNames<Base, Condition> = FilterFlags<Base, Condition>[keyof Base];

export type ValueLoader<T> = (value?: T | IdValue) => Promise<T[]>;
export type IdValue = string | number;
export type ValueType = 'id' | 'entity';

export interface IProps<T = any> extends IComponentProps<T | IdValue> {
    allowEmpty?: boolean;
    emptyText?: string;
    loaderOfValues: ValueLoader<T>;
    textField: keyof T;
    valueField: AllowedNames<T, IdValue>;
    valueType: ValueType;
}
