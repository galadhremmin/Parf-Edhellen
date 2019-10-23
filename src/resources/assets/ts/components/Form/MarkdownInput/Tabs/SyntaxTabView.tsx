import React from 'react';

import TextIcon from '@root/components/TextIcon';
import { LearnMoreMarkdownUrl } from '@root/config';

function SyntaxTabView() {
    return <>
        <p>
            Markdown is a lightweight markup language with plain text formatting syntax.
            It is designed to make it easy for you to apply formatting to your text with
            minimal impact on your content.
        </p>
        <p>
            We support the following Markdown syntax:
        </p>
        <table className="table table-striped">
            <thead>
                <tr>
                    <th>Syntax</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>*Italics*</code></td>
                    <td><em>Italics</em> for emphasis.</td>
                </tr>
                <tr>
                    <td><code>**Bold text**</code></td>
                    <td><b>Bold text</b> for emphasis.</td>
                </tr>
                <tr>
                    <td><code>~~Strike-through~~</code></td>
                    <td><s>Strike-through</s></td>
                </tr>
                <tr>
                    <td><code>![An exclamation](exclamation-glyph.png)</code></td>
                    <td>
                        <TextIcon icon="exclamation-sign" />
                        Displays an image with an alternate text (if the image fails to load).
                    </td>
                </tr>
                <tr>
                    <td><code>[[tree]]</code></td>
                    <td>Link to the dictionary entry for <em>tree</em>.</td>
                </tr>
                <tr>
                    <td><code>[Link to trees](https://en.wikipedia.org/wiki/Tree)</code></td>
                    <td>
                        <a href="https://en.wikipedia.org/wiki/Tree" target="_blank">
                            Link to trees
                        </a>
                        . Links to the specified page.
                        Any external and internal addresses may be specified.
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>
                            * item 1<br />
                            * item 2<br />
                            &nbsp;&nbsp;&nbsp;* sub item 1<br />
                            * item 3
                        </code>
                    </td>
                    <td>
                        <ul>
                            <li>item 1</li>
                            <li>
                                item 2
                                <ul>
                                    <li>sub item 1</li>
                                </ul>
                            </li>
                            <li>item 3</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>
                            1. run<br />
                            2. hide
                        </code>
                    </td>
                    <td>
                        <ol>
                            <li>run</li>
                            <li>hide</li>
                        </ol>
                    </td>
                </tr>
                <tr>
                    <td><code># Header</code></td>
                    <td>1st level header. <em>Please use with care!</em></td>
                </tr>
                <tr>
                    <td><code>## Heareturn <>
                <p>
                    Markdown is a lightweight markup language with plain text formatting syntax.
                    It is designed to make it easy for you to apply formatting to your text with
                    minimal impact on your content.
                </p>
                <p>
                    We support the following Markdown syntax:
                </p>
                <table className="table table-striped">
                    <thead>
                        <tr>
                            <th>Syntax</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>*Italics*</code></td>
                            <td><em>Italics</em> for emphasis.</td>
                        </tr>
                        <tr>
                            <td><code>**Bold text**</code></td>
                            <td><b>Bold text</b> for emphasis.</td>
                        </tr>
                        <tr>
                            <td><code>~~Strike-through~~</code></td>
                            <td><s>Strike-through</s></td>
                        </tr>
                        <tr>
                            <td><code>![An exclamation](exclamation-glyph.png)</code></td>
                            <td>
                                <TextIcon icon="exclamation-sign" />
                                Displays an image with an alternate text (if the image fails to load).
                            </td>
                        </tr>
                        <tr>
                            <td><code>[[tree]]</code></td>
                            <td>Link to the dictionary entry for <em>tree</em>.</td>
                        </tr>
                        <tr>
                            <td><code>[Link to trees](https://en.wikipedia.org/wiki/Tree)</code></td>
                            <td>
                                <a href="https://en.wikipedia.org/wiki/Tree" target="_blank">
                                    Link to trees
                                </a>
                                . Links to the specified page.
                                Any external and internal addresses may be specified.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <code>
                                    * item 1<br />
                                    * item 2<br />
                                    &nbsp;&nbsp;&nbsp;* sub item 1<br />
                                    * item 3
                                </code>
                            </td>
                            <td>
                                <ul>
                                    <li>item 1</li>
                                    <li>
                                        item 2
                                        <ul>
                                            <li>sub item 1</li>
                                        </ul>
                                    </li>
                                    <li>item 3</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <code>
                                    1. run<br />
                                    2. hide
                                </code>
                            </td>
                            <td>
                                <ol>
                                    <li>run</li>
                                    <li>hide</li>
                                </ol>
                            </td>
                        </tr>
                        <tr>
                            <td><code># Header</code></td>
                            <td>1st level header. <em>Please use with care!</em></td>
                        </tr>
                        <tr>
                            <td><code>## Header</code></td>
                            <td>2nd level header. <em>Please use with care!</em></td>
                        </tr>
                        <tr>
                            <td><code>### Header</code></td>
                            <td>3rd level header. <em>Please use with care!</em></td>
                        </tr>
                        <tr>
                            <td><code>@sindarin|mae govannen!@</code></td>
                            <td>
                                Transcribes <em>mae govannen</em> to <span className="tengwar">{'tlE xr^5{#5$Á'}</span>
                                We use Glaemscribe for transcriptions. Supported modes are: {' '}
                                adunaic, blackspeech, quenya, sindarin-beleriand, sindarin, telerin, and westron.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    More information about Markdown
                    {' '}
                    <a href={LearnMoreMarkdownUrl} target="_blank">
                        can be found on Wikipedia
                    </a>.
                </p>
            </>;der</code></td>
                <td>2nd level header. <em>Please use with care!</em></td>
            </tr>
            <tr>
                <td><code>### Header</code></td>
                <td>3rd level header. <em>Please use with care!</em></td>
            </tr>
            <tr>
                <td><code>@sindarin|mae govannen!@</code></td>
                <td>
                    Transcribes <em>mae govannen</em> to <span className="tengwar">{'tlE xr^5{#5$Á'}</span>
                    We use Glaemscribe for transcriptions. Supported modes are: {' '}
                    adunaic, blackspeech, quenya, sindarin-beleriand, sindarin, telerin, and westron.
                </td>
            </tr>
        </tbody>
    </table>
    <p>
        More information about Markdown
        {' '}
        <a href={LearnMoreMarkdownUrl} target="_blank">
            can be found on Wikipedia
        </a>.
    </p>
</>;
}

export default SyntaxTabView;
