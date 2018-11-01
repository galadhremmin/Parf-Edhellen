declare module "html-to-react" {
    export class ProcessNodeDefinitions {
        constructor(react: any);
        processDefaultNode(node: INode, children: INode[]): React.ReactElement<any>;
    }

    export class Parser {
        constructor();
        parseWithInstructions(html: string, include: (n: any) => boolean, instructions: any[]): React.ReactNode;
    }

    export interface IParserInstruction {
        shouldProcessNode(node: INode): boolean;
        processNode(node: INode, children: INode[]): React.ReactElement<any>;
    }

    export interface INode {
        type: string;
        name: string;
        attribs: {
            [ attributeName: string ]: any;
        };
        children: INode[];
        startIndex: number;
    }

    export type ParserInstructions = IParserInstruction[];
}
