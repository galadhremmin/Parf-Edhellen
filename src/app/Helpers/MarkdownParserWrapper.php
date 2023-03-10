<?php

namespace App\Helpers;

use App\Interfaces\IExternalToInternalUrlResolver;
use App\Interfaces\IMarkdownParser;

class MarkdownParserWrapper implements IMarkdownParser
{
    private $_defaultParser;
    private $_parserNoBlocks;

    public function __construct(IExternalToInternalUrlResolver $externalToInternalUrlResolver)
    {
        $this->_defaultParser = new MarkdownParser($externalToInternalUrlResolver);
        $this->_parserNoBlocks = new MarkdownParser($externalToInternalUrlResolver, ['#','>']);
    }

    public function parseMarkdown(string $markdown): string
    {
        return $this->_defaultParser->text($markdown);
    }

    public function parseMarkdownNoBlocks(string $markdown): string
    {
        return $this->_parserNoBlocks->text($markdown);
    }
}
