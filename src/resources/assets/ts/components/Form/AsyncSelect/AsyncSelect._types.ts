import { IComponentProps } from '../FormComponent._types';

type FilterFlags<Base, Condition> = {
    [Key in keyof Base]: Base[Key] extends Condition ? Key : never;
};

type AllowedNames<Base, Condition> = FilterFlags<Base, Condition>[keyof Base];

export type ValueLoader<T> = (value: T) => Promise<T[]>;

export interface IProps<T = any> extends IComponentProps<T> {
    loaderOfValues: ValueLoader<T>;
    textField: keyof T;
    valueField: AllowedNames<T, string | number>;
}
