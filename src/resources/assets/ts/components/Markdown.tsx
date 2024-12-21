/* eslint-disable @typescript-eslint/no-unsafe-member-access */
/* eslint-disable @typescript-eslint/no-unsafe-call */
import { useEffect, useState } from 'react';

import { IMarkdownParserResponse } from '@root/connectors/backend/IUtilityApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { isEmptyString } from '@root/utilities/func/string-manipulation';

import HtmlInject from './HtmlInject';
import {
    IProps,
} from './Markdown._types';

function Markdown(props: IProps) {
    const {
        text,
        parse,
        markdownApi,
    } = props;

    const [ html, setHtml ] = useState<string>(null);
    
    useEffect(() => {
        if (parse && ! isEmptyString(text)) {
            markdownApi?.parseMarkdown({
                markdown: text,
            }).then((response: IMarkdownParserResponse) => {
                setHtml(response.html);
            }).catch((reason: unknown) => {
                setHtml(`The server failed to parse the specified string. Reason: ${JSON.stringify(reason)}`);
            });
        } else {
            setHtml(text);
        }
    }, [ text, parse, markdownApi ]);

    return <>
        {(parse && html) ? <HtmlInject html={html} /> : (html || '')}
    </>;
}

export default withPropInjection(Markdown, {
    markdownApi: DI.UtilityApi,
});
