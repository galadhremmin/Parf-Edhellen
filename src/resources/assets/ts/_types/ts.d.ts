type FirstArgument<T> = T extends (arg1: infer U, ...args: any[]) => any ? U : any;
